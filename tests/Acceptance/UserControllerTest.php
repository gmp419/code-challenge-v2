<?php

namespace Tests\Acceptance;

use App\Models\User\User;
use App\Repositories\UserRepository;
use Tests\FrameworkTest;

class UserControllerTest extends FrameworkTest
{
    /** @var UserRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function testShowRequestReturnsUserData()
    {
        /** @var User $user */
        $user   = $this->userFactory->create();
        $result = $this->get("/api/users/$user->id");
        $result->assertSuccessful();
        $this->assertEquals(
            [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'nickname' => $user->nickname,
            ],
            json_decode($result->getContent(), true)
        );
    }

    public function testUpdateRequestUpdatesUserData()
    {
        /** @var User $user */
        $user   = $this->userFactory->create();
        $data = [
            'id'    => $user->id,
            'name'  => $this->faker->name,
            'email' => $user->email,
            'nickname' => $user->nickname,
        ];
        $result = $this->put("/api/users/$user->id", $data);
        $result->assertSuccessful();
        $this->assertEquals($data, json_decode($result->getContent(), true));
    }

    public function testCreateRequestCreatesUser()
    {
        $data = [
            'name'     => $this->faker->name,
            'email'    => $email = $this->faker->unique()->email,
            'password' => 'hen rooster chicken duck',
            'nickname' => $nickname = $this->faker()->unique()->userName(),
        ];
        $this->assertFalse($this->repository->getModel()->newQuery()->where('email', $email)->exists());
        $this->assertFalse($this->repository->getModel()->newQuery()->where('nickname', $nickname)->exists());
        $result = $this->post("/api/users", $data);
        $result->assertSuccessful();
        $this->assertTrue($this->repository->getModel()->newQuery()->where('email', $email)->exists());
        $this->assertTrue($this->repository->getModel()->newQuery()->where('nickname', $nickname)->exists());
    }

    public function testCreateUserWithDuplicateNicknameWillUpdateNicknameAutomatically()
    {
        $firstUser = User::factory()->create();

        $data = [
            'name'     => $this->faker->name,
            'email'    => $this->faker()->unique()->email(),
            'password' => 'hen rooster chicken duck',
            'nickname' => $firstUser->nickname, // Use the same nickname as the first user
        ];

        $response = $this->post("/api/users", $data);
        $responseData = $response->json();

        $this->assertArrayHasKey('nickname', $responseData);
        $response->assertStatus(200);

        $this->assertTrue(User::where('nickname', $responseData['nickname'])->exists());
    }


}
