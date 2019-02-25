<?php

namespace App\Repositories\Criteria\Reviews;

use App\Repositories\Contracts\RepositoryInterface as Repository;
use App\Repositories\Criteria\Criteria;
use App\Repositories\Eloquent\Enums\Status;
use Setting;

class ActiveOrderedReviews extends Criteria
{
    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where('status', Status::ACTIVE)
                    ->orderBy('is_highlighted', 'DESC')
                    ->orderBy('type_id')
                    ->orderBy('position')
                    ->orderBy('created_at', SORT_DESC)
                    ->limit(Setting::get('review_max_posts'));
    }
}
