<?php

declare(strict_types=1);

namespace Yii\Extension\User\Repository;

use Yii\Extension\User\ActiveRecord\Profile;
use Yii\Extension\User\Form\FormProfile;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\ActiveRecord\ActiveRecordInterface;

final class RepositoryProfile
{
    private ActiveRecordFactory $activeRecordFactory;

    public function __construct(ActiveRecordFactory $activeRecordFactory)
    {
        $this->activeRecordFactory = $activeRecordFactory;
    }

    public function findProfileByCondition(array $condition): ?ActiveRecordInterface
    {
        return $this->profileQuery()->findOne($condition);
    }

    public function loadData(string $id, FormProfile $formProfile): void
    {
        /** @var Profile $profile */
        $profile = $this->findProfileByCondition(['user_id' => (int) $id]);

        $formProfile->name($profile->getName());
        $formProfile->publicEmail($profile->getPublicEmail());
        $formProfile->location($profile->getLocation());
        $formProfile->website($profile->getWebsite());
        $formProfile->bio($profile->getBio());
        $formProfile->timezone($profile->getTimeZone());
    }

    public function update(string $id, FormProfile $formProfile): bool
    {
        /** @var Profile $profile */
        $profile = $this->findProfileByCondition(['user_id' => (int) $id]);

        $profile->name($formProfile->getName());
        $profile->publicEmail($formProfile->getPublicEmail());
        $profile->location($formProfile->getLocation());
        $profile->website($formProfile->getWebsite());
        $profile->bio($formProfile->getBio());
        $profile->timezone($formProfile->getTimeZone());

        return (bool) $profile->update();
    }

    private function profileQuery(): ActiveQueryInterface
    {
        return $this->activeRecordFactory->createQueryTo(Profile::class);
    }
}
