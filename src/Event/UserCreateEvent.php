<?php
    namespace App\Event;

    use App\Entity\User;
    use Symfony\Contracts\EventDispatcher\Event;

    class UserCreateEvent extends Event{
        public const NAME = "user.delete";
        protected User $user;

        public function __construct(User $user)
        {
            $this->user = $user;
        }

        public function getUser(): User
        {
            return $this->user;
        }

        public function __toString(): string
        {
            return sprintf('User ID: %s', $this->user->getId());
        }
    }
?>