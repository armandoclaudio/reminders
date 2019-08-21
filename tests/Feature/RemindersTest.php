<?php

namespace Tests\Feature;

use App\Reminder;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_cannot_create_reminders()
    {
        $this->post(route('reminders.store'))
            ->assertRedirect(route('login'));
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
    public function reminders_data_is_validated()
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
    public function reminders_are_sorted_ascending()
    {
        $this->signIn();
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(15)]);
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::tomorrow()->hour(12)]);
        factory('App\Reminder')->create(['user_id' => $this->user->id, 'due_at' => Carbon::parse('+2 hours')]);

        $response = $this->get(route('reminders.index'));

        $response->assertViewHas('reminders', Reminder::orderBy('due_at')->get());
    }
}
