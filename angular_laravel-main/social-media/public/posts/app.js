angular.module('socialMediaApp', [])
.controller('PostController', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {
    // Initialize scope variables
    $scope.posts = [];
    $scope.newPost = {};
    $scope.loggedInUser = {};
    $scope.notifications = JSON.parse(localStorage.getItem('notifications')) || [];
    $scope.unreadCount = $scope.notifications.filter(n => !n.is_read).length;
    $scope.showDropdown = false;
    $scope.selectedNotification = null;
    $scope.newComment = {};

    // Initialize Pusher for real-time notifications
    const pusher = new Pusher('4cb85e64ec11d57ad432', {
        cluster: 'ap1',
        encrypted: true
    });

    // Subscribe to the notifications channel
    const channel = pusher.subscribe('notifications');

    // Handle PostCreated event
    channel.bind('App\\Events\\PostCreated', function(data) {
        console.log("Notification Data Received:", data);
        if ($scope.loggedInUser.id === data.user_id) return;

        // Create notification for post creation
        const notification = {
            id: `post_${data.post_id}`, // Add ID using post ID
            post_id: data.post_id,
            user_id: data.user_id,
            username: data.username,
            message: data.message,
            time: new Date(data.time).toLocaleTimeString(),
            is_read: false
        };

        // Update notifications
        $scope.notifications.push(notification);
        $scope.unreadCount++;
        localStorage.setItem('notifications', JSON.stringify($scope.notifications));
        $scope.$apply();
    });

    // Handle CommentAdded event
    channel.bind('App\\Events\\CommentAdded', function(data) {
        console.log("Comment Notification Data Received:", data);
        if ($scope.loggedInUser.id === data.user_id) return;

        // Create notification for comment addition
        const notification = {
            id: `comment_${data.comment_id}`, // Add ID using comment ID
            comment_id: data.comment_id,
            user_id: data.user_id,
            username: data.user_name,
            message: data.message,
            post_id: data.post_id,
            time: new Date(data.time).toLocaleTimeString(),
            is_read: false
        };

        // Update notifications
        $scope.notifications.push(notification);
        $scope.unreadCount++;
        localStorage.setItem('notifications', JSON.stringify($scope.notifications));

        // Find the post the comment belongs to and push the new comment to it
        const post = $scope.posts.find(p => p.id === data.post_id);
        if (post) {
            post.comments = post.comments || []; // Ensure it's initialized
            post.comments.push({
                id: data.comment_id,
                user_id: data.user_id,
                user: {
                    id: data.user_id,
                    name: data.user_name
                },
                comment: data.message,
                created_at: data.time
            });
        }

        $scope.$apply(); // Ensure the UI updates
    });

    // Handle LikeAdded event
    channel.bind('App\\Events\\LikeAdded', function(data) {
        console.log("Like Notification Data Received:", data);
        if ($scope.loggedInUser.id === data.user_id) return;

        // Create notification for like addition
        const notification = {
            id: `like_${data.like_id}`, // Add ID using like ID
            like_id: data.like_id,
            user_id: data.user_id,
            username: data.username,
            message: data.message,
            post_id: data.post_id,
            time: new Date(data.time).toLocaleTimeString(),
            is_read: false
        };

        // Update notifications
        $scope.notifications.push(notification);
        $scope.unreadCount++;
        localStorage.setItem('notifications', JSON.stringify($scope.notifications));

        // Find the post that was liked
        const post = $scope.posts.find(p => p.id === data.post_id);
        if (post) {
            post.like_count++;
            post.user_has_liked = true;
        }

        $scope.$apply(); // Ensure the UI updates
    });

    // Toggle notification dropdown visibility
    $scope.toggleDropdown = function() {
        $scope.showDropdown = !$scope.showDropdown;
    };

    // Handle notification click to view
    $scope.viewNotification = function(notification) {
        console.log("Notification Object:", notification); // Log the notification object
    
        if (notification && notification.id) {
            // Proceed to mark as read
            $http.post(`http://127.0.0.1:8000/api/notifications/${notification.id}/mark-as-read`)
                .then(function(response) {
                    console.log("Success:", response.data); // Log success message
                }, function(error) {
                    console.error("Error marking notification as read: ", error);
                });
        } else {
            console.error("Notification ID is undefined"); // Error logging
        }
    };

    // Clear all notifications
    $scope.clearNotifications = function() {
        $scope.notifications = [];
        $scope.unreadCount = 0;
        localStorage.removeItem('notifications');
    };

    // Fetch logged-in user info
    $scope.getLoggedInUser = function() {
        $http.get('/api/user').then(function(response) {
            $scope.loggedInUser = response.data;
        });
    };

    // Fetch all posts
    $scope.getPosts = function() {
        $http.get('/api/posts').then(function(response) {
            $scope.posts = response.data.map(post => ({
                ...post,
                comments: post.comments || [] // Ensure comments are initialized
            }));
        });
    };

    // Create a new post
    $scope.createPost = function() {
        const postData = { content: $scope.newPost.content };

        $http.post('/api/posts', postData)
            .then(function(response) {
                $scope.posts.unshift(response.data);
                $scope.newPost.content = ''; // Clear the input
            })
            .catch(function(error) {
                console.error('Error creating post:', error);
            });
    };

    // Add a comment to a post
    $scope.addComment = function(post) {
        if (!$scope.newComment[post.id]) return; // Check if there's a new comment

        $http.post(`/api/posts/${post.id}/comments`, { comment: $scope.newComment[post.id] })
            .then(function(response) {
                post.comments = post.comments || []; // Ensure comments array exists
                post.comments.push(response.data); // Add the new comment
                $scope.newComment[post.id] = ''; // Clear the input
            })
            .catch(function(error) {
                console.error('Error adding comment:', error);
            });
    };

    // Edit a post
    $scope.editPost = function(post) {
        post.isEditing = true;
        post.originalContent = post.content;
    };

    // Save post after editing
    $scope.savePost = function(post) {
        $http.put(`/api/posts/${post.id}`, { content: post.content })
            .then(function(response) {
                post.isEditing = false;
            })
            .catch(function(error) {
                console.error('Error updating post:', error);
            });
    };

    // Cancel editing a post
    $scope.cancelEdit = function(post) {
        post.content = post.originalContent;
        post.isEditing = false;
    };

    // Like or unlike a post
    $scope.likePost = function(post) {
        const action = post.user_has_liked ? 'unlikePost' : 'like';
        const url = post.user_has_liked ? `/api/posts/${post.id}/unlike` : `/api/posts/${post.id}/like`;

        $http.post(url)
            .then(function(response) {
                post.user_has_liked = !post.user_has_liked; // Toggle the like status
                post.like_count += post.user_has_liked ? 1 : -1; // Update the like count
            })
            .catch(function(error) {
                console.error('Error liking/unliking post:', error);
                post.user_has_liked = !post.user_has_liked; // Revert back
            });
    };

    // Edit a comment
    $scope.editComment = function(comment) {
        comment.isEditing = true;
        comment.originalComment = comment.comment;
    };

    // Save the edited comment
    $scope.saveComment = function(comment, post) {
        $http.put(`/api/posts/${post.id}/comments/${comment.id}`, { comment: comment.comment })
            .then(function(response) {
                comment.isEditing = false;
            })
            .catch(function(error) {
                console.error('Error updating comment:', error);
            });
    };

    // Cancel editing a comment
    $scope.cancelCommentEdit = function(comment) {
        comment.comment = comment.originalComment;
        comment.isEditing = false;
    };

    // Delete a comment
    $scope.deleteComment = function(comment, post) {
        $http.delete(`/api/posts/${post.id}/comments/${comment.id}`)
            .then(function(response) {
                const index = post.comments.indexOf(comment);
                if (index !== -1) {
                    post.comments.splice(index, 1); // Remove comment from the list
                }
            })
            .catch(function(error) {
                console.error('Error deleting comment:', error);
            });
    };

    // Initialize user and posts
    $scope.getLoggedInUser();
    $scope.getPosts();
}]);
