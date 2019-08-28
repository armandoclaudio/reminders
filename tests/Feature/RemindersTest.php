<?php

namespace Tests\Feature;

use App\Jobs\SendReminders;
use App\Notifications\ReminderNotification;
use App\Reminder;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_cannot_create_reminders()
    {
        $this->post(route('reminders.store'),
            factory('App\Reminder')->make()->toArray()
        )->assertRedirect();
    }

    /** @test */
    public function a_user_can_create_reminders()
    {
        $this->signIn();

        $this->post(route('reminders.store'), [
            'title' => 'A reminder',
            'date' => Carbon::tomorrow()->toDateString(),
            'time' => '15:00',
        ]);

        $this->assertDatabaseHas('reminders', [
            'title' => 'A reminder',
            'due_at' => Carbon::tomorrow()->hour(15)->minute(0)->second(0),
        ]);
    }

    /** @test */
    public function reminders_data_is_validated_when_creating_reminders()
    {
        $this->signIn();

        $response = $this->post(route('reminders.store'), [
            'title' => '',
            'date' => '',
            'time' => '',
        ]);

        $response->assertSessionHasErrors([
            'title', 'date', 'time'
        ]);

        $response = $this->post(route('reminders.store'), [
            'date' => 'Invalid Date',
            'time' => 'Invalid Time',
        ]);

        $response->assertSessionHasErrors([
            'date', 'time'
        ]);
    }

    /** @test */
    public function a_user_can_edit_reminders()
    {
        $this->signIn();
        $reminder = factory('App\Reminder')->create([
            'user_id' => $this->user->id,
            'title' => 'Test title',
            'due_at' => Carbon::tomorrow()->hour(12),
        ]);

        $this->patch(route('reminders.update', $reminder->id), [
            'title' => 'New title',
            'date' => Carbon::tomorrow()->toDateString(),
            'time' => '15:00',
        ]);

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'New title',
            'due_at' => Carbon::tomorrow()->hour(15),
        ]);
    }

    /** @test */
    public function reminders_data_is_validated_when_updating_reminders()
    {
        $this->signIn();
        $reminder = factory('App\Reminder')->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->patch(route('reminders.update', $reminder->id), [
            'title' => '',
            'date' => '',
            'time' => '',
        ]);

        $response->assertSessionHasErrors([
            'title', 'date', 'time'
        ]);

        $response = $this->patch(route('reminders.update', $reminder->id), [
            'date' => 'Invalid Date',
            'time' => 'Invalid Time',
        ]);

        $response->assertSessionHasErrors([
            'date', 'time'
        ]);
    }

    /** @test */
    public function a_user_cannot_update_other_users_reminders()
    {
        $this->signIn();
        $reminder = factory('App\Reminder')->create(['title' => 'The title']);

        $response = $this->patch(route('reminders.update', $reminder->id), [
            'title' => 'New title',
            'date' => Carbon::tomorrow()->toDateString(),
            'time' => '15:00',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'The title',
        ]);
    }

    /** @test */
    public function reminders_are_sorted_ascending()
    {
        $this->signIn();
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(15)]);
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(12)]);
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::parse('+2 hours')]);

        $response = $this->get(route('reminders.index'));

        $response->assertViewHas('reminders', Reminder::orderBy('due_at')->get());
    }

    /** @test */
    public function only_future_reminders_are_shown()
    {
        $this->signIn();
        $reminder1 = factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(12)]);
        $reminder2 = factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::yesterday()->hour(15)]);

        $response = $this->get(route('reminders.index'));

        $response->assertSee($reminder1->title);
        $response->assertDontSee($reminder2->title);
    }

    /** @test */
    public function a_reminder_can_be_deleted()
    {
        $this->signIn();
        $reminder = factory('App\Reminder')->create(['user_id' => $this->user->id]);

        $this->delete(route('reminders.destroy', $reminder->id));

        $this->assertDatabaseMissing('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_other_users_reminders()
    {
        $this->signIn();
        $reminder = factory('App\Reminder')->create();

        $response = $this->delete(route('reminders.destroy', $reminder->id));

        $response->assertStatus(401);
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /** @test */
    public function a_mail_notification_is_sent_when_a_reminder_is_due()
    {
        $reminder = factory('App\Reminder')->create(['due_at' => Carbon::now()->seconds(0)]);
        Notification::fake();

        SendReminders::dispatch();

        Notification::assertSentTo(
            $reminder->user,
            ReminderNotification::class,
            function ($notification, $channels) use ($reminder) {
                return $notification->reminder->id === $reminder->id
                    && in_array('mail', $channels);
            }
        );
    }
}
