<?php

namespace App\Repositories\Criteria\Reviews;

use App\Repositories\Contracts\RepositoryInterface as Repository;
use App\Repositories\Criteria\Criteria;

class ReviewsByType extends Criteria
{
    private $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function apply($model, Repository $repository)
    {
        return $model->whereHas('type', function ($query) {
            $query->where('slug', $this->slug);
        });
    }
}
