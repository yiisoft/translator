<?php

declare(strict_types=1);

namespace Yii\Extension\User\Action\Auth;

use Psr\Http\Message\ResponseInterface;
use Yii\Extension\Service\ServiceUrl;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryUser;
use Yiisoft\User\CurrentUser as Identity;

final class Logout
{
    public function run(
        Identity $identity,
        RepositoryUser $repositoryUser,
        ServiceUrl $serviceUrl
    ): ResponseInterface {
        $id = $identity->getId();

        if ($id !== null) {
            /** @var User $user */
            $user = $repositoryUser->findUserById($id);
            $user->updateAttributes(['last_logout_at' => time()]);
            $identity->logout();
        }

        return $serviceUrl->run('home');
    }
}
