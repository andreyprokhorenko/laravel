<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogAttributesDataTable;
use App\Helpers\BreadcrumbsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogAttribute;
use App\Repositories\Eloquent\{
    BlogAttributesRepository, Models\BlogAttribute
};
use App\Services\BlogAttributeService;
use App\Services\PostCategoryAttributeService;
use App\Services\PostCategoryService;

class BlogAttributesController extends Controller
{
    /**
     * Current page breadcrumbs
     *
     * @var array
     */
    protected $breadcrumbs = [
        [
            'name' => 'Blog Attributes Management',
            'route' => 'blog-attributes.index'
        ]
    ];

    /**
     *  Blog attributes repository instance
     *
     * @var BlogAttributesRepository
     */
    protected $blogAttributesRepository;

    /** @var BlogAttributeService  */
    protected $blogAttributeService;

    /** @var PostCategoryService  */
    protected $postCategoryService;

    /** @var PostCategoryAttributeService  */
    protected $postCategoryAttributeService;

    public function __construct(
        BlogAttributesRepository $blogAttributesRepository,
        BlogAttributeService $blogAttributeService,
        PostCategoryService $postCategoryService,
        PostCategoryAttributeService $postCategoryAttributeService
    ) {
        $this->postCategoryAttributeService = $postCategoryAttributeService;
        $this->blogAttributeService = $blogAttributeService;
        $this->postCategoryService = $postCategoryService;
        $this->blogAttributesRepository = $blogAttributesRepository;
        $this->middleware('permission:read-blogAttributes')->only('index', 'show');
        $this->middleware('permission:create-blogAttributes')->only('create', 'store');
        $this->middleware('permission:update-blogAttributes')->only('edit', 'update');
        $this->middleware('permission:delete-blogAttributes')->only('destroy');
    }

    /**
     * Display a listing of the blog attributes.
     *
     * @param BlogAttributesDataTable $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(BlogAttributesDataTable $dataTable)
    {
        return $dataTable->render('admin.datatables.table', [
            'breadcrumbs' => $this->breadcrumbs,
            'entityName' => BlogAttribute::getTableName()
        ]);
    }

    /**
     * Show the form for creating a new blog attribute.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, 'Create a Blog Attribute');

        return view('admin.blog-attributes.create', [
            'breadcrumbs' => $this->breadcrumbs,
            'attribute' => new BlogAttribute,
            'postCategorySlugs' => $this->postCategoryService->getForSelect(),
        ]);
    }

    /**
     * Store a newly created blog attribute in storage.
     *
     * @param  StoreBlogAttribute $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogAttribute $request)
    {
        $this->blogAttributeService->createByRequestData($request->toArray(), $this->getLoggedInUserId());

        return redirect()->route('blog-attributes.index')
            ->with('success-msg', 'Blog attribute created successfully.');
    }

    /**
     * Show the form for editing the specified blog attribute.
     *
     * @param  BlogAttribute $blogAttribute
     * @return \Illuminate\Http\Response
     */
    public function edit(BlogAttribute $blogAttribute)
    {
        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, 'Edit Blog Attribute');

        return view('admin.blog-attributes.edit', [
            'breadcrumbs' => $this->breadcrumbs,
            'attribute' => $blogAttribute,
            'postCategorySlugs' => $this->postCategoryService->getForSelectByAttribute($blogAttribute),
        ]);
    }

    /**
     * Update the specified blog attribute in storage.
     *
     * @param  StoreBlogAttribute $request
     * @param  int $attributeId
     * @return \Illuminate\Http\Response
     */
    public function update(StoreBlogAttribute $request, $attributeId)
    {
        $updateArr = $request->validated();

        if ($this->blogAttributeService->updateByRequestData($updateArr, $attributeId, $this->getLoggedInUserId())) {
            return redirect()->route('blog-attributes.index')->with('success-msg', 'Blog attribute updated successfully.');
        }
        return back()->withInput()->with('error-msg', 'Something went wrong.');
    }

    public function getAttributesByCategory(int $categoryId)
    {
        $attributes = $this->postCategoryAttributeService->getAttributeTitlesByCategoryId($categoryId);

        return response()->json([
            'attributes' => $attributes,
        ]);
    }

    /**
     * Remove the specified blog attribute from storage.
     *
     * @param  int $attributeId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $attributeId)
    {
        if ($this->blogAttributesRepository->delete($attributeId)) {
            return response()->json(['success' => 'Blog attribute deleted successfully.']);
        }
        return response()->json(['error' => 'An error occured while deleting record.']);
    }
}
