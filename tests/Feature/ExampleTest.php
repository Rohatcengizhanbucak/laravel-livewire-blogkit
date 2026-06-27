<?php

namespace Tests\Feature;

use App\Models\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        Locale::query()->create([
            'code' => 'en',
            'name' => 'English',
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertRedirectToRoute('blog.index', ['locale' => 'en']);
    }
}
