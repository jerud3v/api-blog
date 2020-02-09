<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    private function data() {
        return [
            'title' => 'Test Title',
            'content' => 'Test. Lorem ipsum dolor sit amet.',
            'api_token' => $this->user
        ];
    }

    /** @test */
    public function only_published_post_can_be_fetched_by_unauthenticated_user() {
        $this->withoutExceptionHandling();
        
        factory(Post::class, 5)->create();
        
        $response = $this->get('/api/posts');
        
        $response->assertJson([
            'data' => [
                [
                    'data' => [
                        'status' => true
                    ]
                ]
            ]
        ]); 
    }

    /** @test */
    public function unauthenticated_user_is_restricted_to_create_post_and_will_be_redirected_to_login_page() {        
        $response = $this->post('/api/posts', array_merge($this->data(), ['api_token' => '']));
        
        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
        $this->assertCount(0, Post::all());
    }

    /** @test */
    public function post_form_required_fields() {
        collect(['title', 'content'])
            ->each(function($field) {
                $response = $this->post('/api/posts', array_merge($this->data(), [$field => ''])
                );

                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Post::all());
            });
    }

    /** @test */
    public function authenticated_user_can_add_post() {
        $this->withoutExceptionHandling();
        
        $response = $this->post('/api/posts', $this->data());
        
        $post = Post::first();
        
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals('Test Title', $post->title);
        $this->assertEquals('Test. Lorem ipsum dolor sit amet.', $post->content);
    }

    /** @test */
    public function authenticated_user_can_retrieve_post() {
        $this->withoutExceptionHandling();
        $post = factory(Post::class)->create([ 'user_id' => $this->user->id ]);
        $reponse = $this->get('/api/posts/' . $post->id . '?api_token=' . $this->user->api_token);
        
        
        $reponse->assertStatus(Response::HTTP_OK);
        $reponse->assertJson([
            'data' => [
                'title' => $post->title,
                'content' => $post->content
            ]
        ]);
    }

    /** @test */
    public function only_authenticated_post_owner_can_update_own_post() {
        $otherUser = factory(User::class)->create();

        $post = factory(Post::class)->create([ 'user_id' => $this->user->id ]);
        $response = $this->patch('/api/posts/' . $post->id, array_merge($this->data(), [
            'api_token' => $otherUser->api_token
        ]));

        $post = $post->fresh();
        
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function only_authenticated_post_owner_can_delete_own_post() {
        $otherUser = factory(User::class)->create();

        $post = factory(Post::class)->create([ 'user_id' => $this->user->id]);
        $response = $this->delete('/api/posts/' . $post->id, [ 'api_token' => $otherUser->api_token ]);
        
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertCount(1, Post::all());
    }
}
