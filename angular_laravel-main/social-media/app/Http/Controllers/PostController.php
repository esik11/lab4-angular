<?php

namespace App\Http\Controllers;
use App\Events\NotificationSent;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Events\PostCreated;
use App\Events\LikeAdded;
use App\Events\CommentAdded;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import User model
class PostController extends Controller
{
    // Fetch all posts with the user who created them and comments
    public function index()
    {
        $user = Auth::user();
        
        // Eager-load 'user' with 'name' and 'profile' with 'profile_picture'
        $posts = Post::with([
            'user' => function ($query) {
                $query->select('id', 'name') // Selecting only 'id' and 'name' from 'users'
                      ->with('profile:id,user_id,profile_picture'); // Eager-load 'profile' with 'profile_picture'
            },
            'comments.user' => function ($query) {
                $query->select('id', 'name'); // Ensure comments' users have 'name' loaded
            }
        ])->get();
    
        // Append a "user_has_liked" field to each post based on the logged-in user
        $posts->each(function ($post) use ($user) {
            $post->user_has_liked = $post->likes()->where('user_id', $user->id)->exists();
        });
    
        return response()->json($posts);
    }
    // Store a new post with user_id and trigger PostCreated event
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'content' => 'required|string|max:255',
        ]);

        // Create post
        $post = Post::create([
            'content' => $request->content,
            'user_id' => auth()->id(),
        ]);

        // Check if the post was created successfully
        if ($post) {
            $user = Auth::user();

            // Dispatch the PostCreated event
            broadcast(new PostCreated($user, $post))->toOthers();

            return response()->json($post->load('user'));
        }

        return response()->json(['message' => 'Error creating post'], 500);
    }

    // Update an existing post (only by the owner)
    public function update(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:255',
        ]);

        $post = Post::findOrFail($postId);

        // Ensure only the post owner can update the post
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->content = $request->input('content');
        $post->save();

        return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
    }

    // Like or Unlike a post
    public function likePost($postId)
    {
        try {
            $post = Post::findOrFail($postId);
            $user = Auth::user();
    
            // Toggle like
            $like = $post->likes()->where('user_id', $user->id)->first();
            if ($like) {
                // Unlike the post
                $like->delete();
            } else {
                // Like the post
                $like = Like::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
                broadcast(new LikeAdded($user, $like))->toOthers();
            }
    
            return response()->json(['like_count' => $post->likes()->count()]);
        } catch (\Exception $e) {
            \Log::error('Like Post Error: ' . $e->getMessage(), [
                'postId' => $postId,
                'userId' => Auth::id(),
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    public function unlikePost($postId)
{
    $post = Post::findOrFail($postId);
    $user = auth()->user(); 

    // Check if the post is liked by the user
    $like = $post->likes()->where('user_id', $user->id)->first();

    if ($like) {
        $like->delete();
        return response()->json(['message' => 'Post unliked successfully'], 200);
    }

    return response()->json(['message' => 'You have not liked this post'], 400);
}

    // Add a comment to a post
    public function addComment(Request $request, $postId)
{
    $request->validate([
        'comment' => 'required|string|max:255',
    ]);

    $post = Post::findOrFail($postId);
    $user = Auth::user();

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'comment' => $request->comment,
    ]);

    // Dispatch the CommentAdded event
    broadcast(new CommentAdded($user, $comment))->toOthers();

    return response()->json($comment);
}
    // Edit an existing post (only by the owner)
    public function edit(Request $request, Post $post)
{
    // Ensure only the post owner can edit the post
    if ($post->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Perform validation
    $request->validate([
        'content' => 'required|string|max:255',
    ]);

    $post->content = $request->input('content');
    $post->save();

    return response()->json($post);
}
// Edit an existing comment (only by the owner)
public function editComment(Request $request, Post $post, Comment $comment)
{
    // Ensure only the comment owner can edit the comment
    if ($comment->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Perform validation
    $request->validate([
        'comment' => 'required|string|max:255', // Validate the 'comment' field
    ]);

    // Update the comment
    $comment->comment = $request->input('comment'); // Use 'comment' field from request
    $comment->save();

    return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
}

// Delete a comment (only by the owner)
public function deleteComment(Post $post, Comment $comment)
{
    // Ensure only the comment owner can delete the comment
    if ($comment->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete the comment
    $comment->delete();

    return response()->json(['message' => 'Comment deleted successfully'], 200);
}
}