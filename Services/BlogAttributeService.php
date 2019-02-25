<?php

namespace App\Services;

use App\Repositories\Eloquent\BlogAttributesRepository;
use App\Repositories\Eloquent\Models\BlogAttribute;
use App\Repositories\Eloquent\PostCategoryAttributeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class BlogAttributeService extends AbstractService
{
    /** @var BlogAttributesRepository */
    protected $blogAttributesRepository;
    /** @var PostCategoryAttributeRepository */
    protected $postCategoryAttributeRepository;

    public function __construct(
        BlogAttributesRepository $blogAttributesRepository,
        PostCategoryAttributeRepository $postCategoryAttributeRepository
    ) {
        $this->blogAttributesRepository = $blogAttributesRepository;
        $this->postCategoryAttributeRepository = $postCategoryAttributeRepository;
    }

    /**
     * @param int $postCategoryId
     * @param array $columns
     * @return Collection|BlogAttribute[]
     */
    public function getByPostCategoryId(int $postCategoryId, array $columns = ['id', 'title'])
    {
        $postCategoryAttributes = $this->postCategoryAttributeRepository->findWhere([
            'post_category_id' => $postCategoryId,
        ])->all();

        if ($postCategoryAttributes) {
            $attributes = BlogAttribute::whereIn('id', array_column($postCategoryAttributes, 'attribute_id'))->get($columns);
        } else {
            $attributes = $this->blogAttributesRepository->all($columns);
        }

        return $attributes;
    }

    public function getColumnListing(): array
    {
        return Schema::getColumnListing(BlogAttribute::getTableName());
    }

    public function getAllTitles(): array
    {
        return BlogAttribute::pluck('title', 'id')->toArray();
    }

    public function updateByRequestData(array $data, int $id, int $userId): bool
    {
        /** @var PostCategoryAttributeService $postCategoryAttributeService */
        $postCategoryAttributeService = self::getService(PostCategoryAttributeService::class);

        try {
            \DB::beginTransaction();

            $postCategoryAttributes = $data['post_category_attribute'] ?? [];
            $postCategoryAttributeService->updateByAttributeId($postCategoryAttributes, $id);

            $availableColumns = array_flip($this->getColumnListing());
            $updateData = array_intersect_key($data, $availableColumns);
            $updateData['updated_by'] = $userId;

            $this->blogAttributesRepository->update($updateData, $id);

        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }

        \DB::commit();
        return true;
    }

    public function createByRequestData(array $data, int $userId): bool
    {
        /** @var PostCategoryAttributeService $postCategoryAttributeService */
        $postCategoryAttributeService = self::getService(PostCategoryAttributeService::class);

        try {
            \DB::beginTransaction();

            $availableColumns = array_flip($this->getColumnListing());
            $modelData = array_intersect_key($data, $availableColumns);

            $modelData['created_by'] = $userId;
            $modelData['translated_title'] = $modelData['title'] ?? '';

            /** @var BlogAttribute $blogAttribute */
            $blogAttribute = $this->blogAttributesRepository->create($modelData);

            $postCategoryAttributes = $data['post_category_attribute'] ?? [];
            $postCategoryAttributeService->addByAttributeId($postCategoryAttributes, $blogAttribute->id);

        } catch (\Exception $e) {
            \DB::rollBack();
            return false;
        }

        \DB::commit();
        return true;
    }

}