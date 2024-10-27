angular.module('socialMediaApp').controller('NotificationController', ['$scope', 'notificationService', function($scope, notificationService) {
    $scope.notifications = [];
    $scope.unreadCount = 0;
    $scope.showDropdown = false;

    // Initialize Pusher
    var pusher = new Pusher('4cb85e64ec11d57ad432', {
        cluster: 'ap1',
        encrypted: true
    });

    var channel = pusher.subscribe('notify-channel'); // Ensure this matches your Laravel broadcast channel

    // Listen for new notifications
    channel.bind('NotificationSent', function(data) {
        $scope.notifications.push(data.notification); // Add new notification to the list
        $scope.unreadCount++;
        $scope.$apply(); // Update AngularJS scope
    });

    // Fetch notifications from the server
    $scope.fetchNotifications = function() {
        notificationService.fetchNotifications().then(function(response) {
            $scope.notifications = response.data;
            $scope.unreadCount = $scope.notifications.filter(n => !n.is_read).length;
        });
    };

    // Mark notification as read
    $scope.markAsRead = function(notificationId) {
        notificationService.markAsRead(notificationId).then(function() {
            $scope.fetchNotifications(); // Refresh notifications after marking one as read
        });
    };

    // Toggle the dropdown
    $scope.toggleDropdown = function() {
        $scope.showDropdown = !$scope.showDropdown;
    };

    // Initial fetch of notifications
    $scope.fetchNotifications();
}]);
