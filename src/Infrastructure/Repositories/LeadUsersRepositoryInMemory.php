<?php

namespace Infrastructure\Repositories;

use Illuminate\Contracts\Cache\Repository as Cache;

final class LeadUsersRepositoryInMemory
{

    public function __construct(private LeadUsersRepository $leadUsersRepository, private Cache $cache)
    {
    }

    public function get(string $userId)
    {
        $cacheKey = sprintf(CacheUtils::LEAD_USER, $userId);

        return $this->cache->remember($cacheKey, CacheUtils::TTL, function () use ($userId) {
            return $this->leadUsersRepository->get($userId);
        });
    }

    public function create(string $userId, string $leadId)
    {
        $this->leadUsersRepository->create($userId, $leadId);
    }
}
