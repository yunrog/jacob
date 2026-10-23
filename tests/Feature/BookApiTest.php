<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_can_be_created_and_retrieved(): void
    {
        $response = $this->postJson('/api/books', [
            'title' => 'Belajar Laravel',
            'description' => 'Panduan Laravel',
            'status' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Belajar Laravel');

        $book = Book::firstOrFail();

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('description', 'Panduan Laravel');
    }

    public function test_books_can_be_listed_updated_and_deleted(): void
    {
        $book = Book::create(['title' => 'Judul lama']);

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Judul lama']);

        $this->patchJson("/api/books/{$book->id}", ['title' => 'Judul baru'])
            ->assertOk()
            ->assertJsonPath('title', 'Judul baru');

        $this->deleteJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Book deleted');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_book_creation_requires_a_title(): void
    {
        $this->postJson('/api/books', ['description' => 'Tanpa judul'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }
}
