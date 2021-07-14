<?php
    namespace App\Event;

    use App\Entity\User;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Contracts\EventDispatcher\Event;
    
    class UserCreateEvent extends Event{
        public const NAME = "user.create";
        protected User $user;
        protected Response $response;
        public function __construct(User $user, Response $response)
        {
            $this->user = $user;
            $this->response = $response;
        }

        public function getUser(): User
        {
            return $this->user;
        }
        public function getResponse(): Response
        {
            return $this->response;
        }
        public function setResponse(Response $response): void
        {
            $this->response = $response;
        }
        public function __toString(): string
        {
            return sprintf('User ID: %s', $this->user->getId());
        }
    }
?>