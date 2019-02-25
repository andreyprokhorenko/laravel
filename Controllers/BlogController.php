<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\PostsDataTable;
use App\Helpers\BreadcrumbsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CopyBlogPost;
use App\Http\Requests\StoreBlogPost;
use App\Repositories\Criteria\Blogs\ActiveOrderedAttributes;
use App\Repositories\Criteria\Common\ActiveOrderedLanguages;
use App\Repositories\Eloquent\{
    BlogAttributesRepository, BlogRepository, Enums\BlogType, Enums\Status, LanguagesRepository, Models\AttributeBlog, Models\Blog, Models\BlogAttribute, Models\Common, Models\PostCategory, PostCategoryAttributeRepository, TagsRepository
};
use App\Services\BlogAttributeService;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Image;

class BlogController extends Controller
{
    /**
     * Current page breadcrumbs
     *
     * @var array
     */
    protected $breadcrumbs = [
        [
            'name' => 'Blog management',
            'route' => 'blog.index'
        ]
    ];

    /**
     * Blog repository instance
     *
     * @var BlogRepository
     */
    protected $blogRepository;

    /**
     * Languages repository instance
     *
     * @var LanguagesRepository
     */
    protected $languagesRepository;

    /** @var BlogAttributeService  */
    protected $blogAttributeService;

    protected $tagsRepository;
    protected $blogAttributesRepository;

    /** @var PostCategoryAttributeRepository  */
    protected $postCategoryAttributeRepository;

    public function __construct(
        BlogRepository $blogRepository,
        LanguagesRepository $languagesRepository,
        TagsRepository $tagsRepository,
        BlogAttributesRepository $blogAttributesRepository,
        PostCategoryAttributeRepository $postCategoryAttributeRepository,
        BlogAttributeService $blogAttributeService
    ) {
        $this->blogRepository = $blogRepository;
        $this->postCategoryAttributeRepository = $postCategoryAttributeRepository;
        $this->blogAttributeService = $blogAttributeService;
        $this->languagesRepository = $languagesRepository;
        $this->tagsRepository = $tagsRepository;
        $this->blogAttributesRepository = $blogAttributesRepository;
        $this->middleware('permission:read-blog')->only('index','show');
        $this->middleware('permission:create-blog')->only('create','store','showCopy','postCopy');
        $this->middleware('permission:update-blog')->only('edit','update');
        $this->middleware('permission:delete-blog')->only('destroy');
    }

    /**
     * Display a listing of the blog posts.
     *
     * @param PostsDataTable $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(PostsDataTable $dataTable)
    {
        return $dataTable->render('admin.datatables.table', [
            'breadcrumbs' => $this->breadcrumbs,
            'entityName' => Blog::getTableName()
        ]);
    }

    /**
     * Show the form for creating a new blog post.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, 'Create new post');

        $languages = $this->getLanguages();

        $categories = PostCategory::orderBy('name')->get();
        $firstCategoryId = $categories->count() ? $categories->id : 0;
        if (!$languages->count() || !$categories->count()) {
            return back()->with('error-msg', 'Please check are there blog category and language.');
        }

        $this->appendAppEntryPoint();

        return view('admin.blog.create', [
            'breadcrumbs' => $this->breadcrumbs,
            'blog' => new Blog(),
            'languages' => $languages,
            'attributes' => $this->blogAttributeService->getByPostCategoryId($firstCategoryId),
            'postCategories' => $categories,
        ]);
    }

    /**
     * Store a newly created blog post in storage.
     *
     * @param  StoreBlogPost $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogPost $request)
    {
        $blogPost = $this->insertBlogPost($request);
        if ($blogPost) {
            $this->insertTagsAndAttributes($request, $blogPost);
        }

        return redirect()->route('blog.index')->with('success-msg', 'Blog post created successfully.');
    }

    private function insertBlogPost(Request $request)
    {
        $images = $this->uploadImageAndGetFileNames($request);
        $image = $images['image'];
        $thumbnail = $images['thumbnail'];

        $blogPost = $this->blogRepository->create([
            'title' => $request->get('title'),
            'content' => $request->get('content'),
            'slug' => Common::generateSlug($request->get('slug')),
            'image' => $image,
            'thumbnail' => $thumbnail,
            'category_id' => $request->get('category_id'),
            'language_id' => $request->get('language_id'),
            'is_highlighted' => $request->get('is_highlighted'),
            'status' => (int) $request->get('status'),
            'created_by' => Auth::id()
        ]);

        return $blogPost;
    }

    private function uploadImageAndGetFileNames(Request $request)
    {
        if ($request->file('icon')) {
            $file = $request->file('icon');
            if ($request->get('id')) {
                $imageNamePart = $request->get('id');
            } else {
                $imageNamePart = $request->get('title');
            }
            $ext = $file->guessClientExtension();
            $image = md5(mktime(true) . $imageNamePart . str_random(10)) . '.' . $ext;
            $file->move(Blog::IMAGES_FOLDER, $image);

            $thumbnail = $this->generateThumbnail($image);
        } else {
            $image = null;
            $thumbnail = null;
        }

        return [
            'image' => $image,
            'thumbnail' => $thumbnail
        ];
    }

    private function insertTagsAndAttributes(Request $request, $blogPost)
    {
        if (!is_null($request->get('tags'))) {
            $tagsIds = $this->tagsRepository->addNonExistingTags($request->get('tags'));

            foreach ($tagsIds as $tagId) {
                $blogPost->tags()->attach($blogPost->id, [
                    'tag_id' => $tagId
                ]);
            }
        }

        if (!is_null($request->get('attributes'))) {
            $this->blogRepository->syncAttributes($blogPost->id, $request->get('attributes'));
        }
    }

    /**
     * Display the specified blog post.
     *
     * @param  Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, $blog->getAttribute('title'));

        return view('admin.blog.preview', [
            'blog' => $blog,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * Show the form for editing the specified blog post.
     *
     * @param  Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog)
    {
        if (Auth::user()->hasRole('writer') && $blog->created_by != Auth::user()->id) {
            abort(403);
        }

        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, 'Edit blog post');

        $blogAttributes = $this->getBlogAttributes($blog);

        $languages = $this->getLanguages();

        $this->appendAppEntryPoint('blog.create');

        return view('admin.blog.edit', [
            'blog' => $blog,
            'breadcrumbs' => $this->breadcrumbs,
            'languages' => $languages,
            'attributes' => $this->blogAttributeService->getByPostCategoryId($blog->category_id),
            'blogAttributes' => $blogAttributes,
            'postCategories' => PostCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified blog post in storage.
     *
     * @param  StoreBlogPost $request
     * @param  int $blogPostId
     * @throws \Exception
     * @return \Illuminate\Http\Response
     */
    public function update(StoreBlogPost $request, int $blogPostId)
    {
        $blogPost = $this->blogRepository->find($blogPostId);
        if ($blogPost) {
            if (Auth::user()->hasRole('writer') && $blogPost->created_by != Auth::user()->id) {
                abort(403);
            }

            $blogPost = $this->updatePostData($request, $blogPostId);
            if ($blogPost) {
                $this->updateTagsAndAttributes($request, $blogPostId);
                return redirect()->route('blog.index')->with('success-msg', 'Blog post updated successfully.');
            }
        } else {
            return back()->withInput()->with('error-msg', 'Something went wrong.');
        }
    }

    /**
     * Show copy page
     */
    public function showCopy(int $id)
    {
        $post = $this->blogRepository->find($id);
        if (!$post) {
            abort(404);
        }

        BreadcrumbsHelper::createBreadcrumb($this->breadcrumbs, 'Copy blog post');

        $languages = $this->getLanguages();

        return view('admin.blog.copy', [
            'breadcrumbs' => $this->breadcrumbs,
            'languages' => $languages,
            'post' => $post,
        ]);
    }

    /**
     * Remove the specified blog post from storage.
     *
     * @param  int $blogPostId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $blogPostId)
    {
        $this->blogRepository->deleteRelatedTags($blogPostId);
        $blogPost = $this->blogRepository->find($blogPostId);
        if ($blogPost) {
            $blogPost->status = Status::DELETED;
            $blogPost->updated_by = Auth::id();
            if ($blogPost->save()) {
                return response()->json(['success' => 'Blog post deleted successfully.']);
            }
        }
        return response()->json(['success' => 'An error occured while deleting blog post.']);
    }

    public function language($blogPostId)
    {
        /** @var Blog $blog */
        $blog = $this->blogRepository->find($blogPostId);
        if ($blog) {
            $languages = $this->languagesRepository->findWhere([
                ['id', '<>', $blog->language->getKey()]
            ]);

            if (Auth::user()->hasRole('writer')) {
                $languages = Auth::user()->writerLanguages
                    ->where('id', '!=', $blog->language->getKey());
            };

            return view('admin.blog.language', [
                'blogId' => $blogPostId,
                'languages' => $languages,
                'breadcrumbs' => $this->breadcrumbs
            ]);
        }
        return redirect()->route('blog.index')->with('error-msg', 'Blog post not found');
    }
}
