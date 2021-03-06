<?php

declare(strict_types=1);

namespace App\User\Domain\UseCase\AVisitorWantsToSignup;

use App\Shared\Domain\Clock;
use App\Shared\Domain\IdGenerator;
use App\Shared\Domain\Notifier;
use App\Shared\Domain\RandomGenerator;
use App\Shared\Domain\UseCase\UseCaseHandler;
use App\User\Domain\Data\Model\User;
use App\User\Domain\Data\Repository\Users;
use App\User\Domain\Email\Emails\UserRegisterEmail;
use App\User\Domain\Email\Mailer;

class Handler implements UseCaseHandler
{
    private Users $users;
    private IdGenerator $idGenerator;
    private Clock $clock;
    private RandomGenerator $randomGenerator;
    private Mailer $mailer;
    private Notifier $notifier;

    public function __construct(Users $user, IdGenerator $idGenerator, Clock $clock, RandomGenerator $randomGenerator, Mailer $mailer, Notifier $notifier)
    {
        $this->users           = $user;
        $this->idGenerator     = $idGenerator;
        $this->clock           = $clock;
        $this->randomGenerator = $randomGenerator;
        $this->mailer          = $mailer;
        $this->notifier        = $notifier;
    }

    public function __invoke(Input $input): void
    {
        $user = User::register(
            $this->idGenerator->getNew(),
            $input->getUserFirstname(),
            $input->getUserLastname(),
            $input->getUserEmail(),
            $this->clock->now(),
            $this->randomGenerator->generate(64)
        );
        $this->users->add($user);

        $this->mailer->send(new UserRegisterEmail($user));
        $this->notifier->notify(Notifier::TYPE_SUCCESS, 'Registration successful, please check your inbox !');
    }
}
