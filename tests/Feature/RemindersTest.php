<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Notifications\ReminderNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Reminder;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_cannot_create_reminders()
    {
        $this->post(route('reminders.store'),
            Reminder::factory()->make()->toArray()
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
            'repeats' => '2 months',
        ]);

        $this->assertDatabaseHas('reminders', [
            'title' => 'A reminder',
            'due_at' => Carbon::tomorrow()->hour(15)->minute(0)->second(0),
            'repeats' => '2 months',
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
    public function reminders_repeats_is_validated()
    {
        $this->signIn();

        $response = $this->post(route('reminders.store'), ['repeats' => 'Invalid repeat']);
        $response->assertSessionHasErrors(['repeats']);

        $response = $this->post(route('reminders.store'), ['repeats' => '1 test']);
        $response->assertSessionHasErrors(['repeats']);

        $response = $this->post(route('reminders.store'), ['repeats' => '-1 days']);
        $response->assertSessionHasErrors(['repeats']);

        $response = $this->post(route('reminders.store'), ['repeats' => '3.26 months']);
        $response->assertSessionHasErrors(['repeats']);

        $response = $this->post(route('reminders.store'), ['repeats' => '']);
        $response->assertSessionDoesntHaveErrors(['repeats']);

        $reminder = Reminder::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $response = $this->post(route('reminders.update', $reminder->id), ['repeats' => '1 week']);
        $response->assertSessionDoesntHaveErrors(['repeats']);
    }

    /** @test */
    public function a_user_can_edit_reminders()
    {
        $this->signIn();
        $reminder = Reminder::factory()->create([
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
        $reminder = Reminder::factory()->create([
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
        $reminder = Reminder::factory()->create(['title' => 'The title']);

        $response = $this->patch(route('reminders.update', $reminder->id), [
            'title' => 'New title',
            'date' => Carbon::tomorrow()->toDateString(),
            'time' => '15:00',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'The title',
        ]);
    }

    /** @test */
    public function only_future_reminders_are_shown()
    {
        $this->signIn();
        $reminder1 = Reminder::factory()->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(12)]);
        $reminder2 = Reminder::factory()->create(['user_id' => $this->user->id, 'due_at' => Carbon::yesterday()->hour(15)]);

        $response = $this->get(route('reminders.index'));

        $response->assertSee($reminder1->title);
        $response->assertDontSee($reminder2->title);
    }

    /** @test */
    public function a_reminder_can_be_deleted()
    {
        $this->signIn();
        $reminder = Reminder::factory()->create(['user_id' => $this->user->id]);

        $this->delete(route('reminders.destroy', $reminder->id));

        $this->assertDatabaseMissing('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_other_users_reminders()
    {
        $this->signIn();
        $reminder = Reminder::factory()->create();

        $response = $this->delete(route('reminders.destroy', $reminder->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
        ]);
    }

    /** @test */
    public function a_mail_notification_is_sent_when_a_reminder_is_due()
    {
        $reminder = Reminder::factory()->create(['due_at' => Carbon::now()->seconds(0)]);
        Notification::fake();

        $this->artisan('schedule:run');

        Notification::assertSentTo(
            $reminder->user,
            ReminderNotification::class,
            function ($notification, $channels) use ($reminder) {
                return $notification->reminders[0]['title'] === $reminder->title
                    && in_array('mail', $channels);
            }
        );
    }

    /** @test */
    public function reminders_for_the_same_time_are_grouped_in_a_single_notfication()
    {
        $user = User::factory()->create();
        $reminder1 = Reminder::factory()->create(['user_id' => $user->id, 'title' => 'This is the first reminder', 'due_at' => Carbon::now()->seconds(0)]);
        $reminder2 = Reminder::factory()->create(['user_id' => $user->id, 'title' => 'This is the second reminder', 'due_at' => Carbon::now()->seconds(0)]);
        Notification::fake();

        $this->artisan('schedule:run');

        Notification::assertSentTo(
            $user,
            ReminderNotification::class,
            function ($notification) use ($reminder1, $reminder2) {
                return $notification->reminders[0]['title'] == $reminder1->title
                    && $notification->reminders[1]['title'] == $reminder2->title;
            }
        );
    }

    /** @test */
    public function reminders_can_be_repeated_every_month()
    {
        $reminder = Reminder::factory()->create([
            'due_at' => Carbon::now()->seconds(0),
            'repeats' => '1 months',
        ]);
        Notification::fake();

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $reminder->user_id,
            'title' => $reminder->title,
            'due_at' => Carbon::now()->seconds(0)->addMonths(1),
        ]);
    }

    /** @test */
    public function reminders_can_be_repeated_every_2_weeks()
    {
        $reminder = Reminder::factory()->create([
            'due_at' => Carbon::now()->seconds(0),
            'repeats' => '2 weeks',
        ]);
        Notification::fake();

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $reminder->user_id,
            'title' => $reminder->title,
            'due_at' => Carbon::now()->seconds(0)->addWeeks(2),
        ]);
    }

    /** @test */
    public function reminders_can_be_repeated_every_year()
    {
        Carbon::setTestNow('2021-11-13 10:00:00');

        $reminder = Reminder::factory()->create([
            'due_at' => '2021-11-13 10:00:00',
            'repeats' => '1 years',
        ]);
        Notification::fake();

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $reminder->user_id,
            'title' => $reminder->title,
            'due_at' => '2022-11-13 10:00:00',
        ]);
    }

    /** @test */
    public function reminders_can_be_repeated_every_15_days()
    {
        $reminder = Reminder::factory()->create([
            'due_at' => Carbon::now()->seconds(0),
            'repeats' => '15 days',
        ]);
        Notification::fake();

        $this->artisan('schedule:run');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $reminder->user_id,
            'title' => $reminder->title,
            'due_at' => Carbon::now()->seconds(0)->addDays(15),
        ]);
    }
}
