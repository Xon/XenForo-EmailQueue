XenForo-EmailQueue
======================

This addon ensures that the following email sources go via the email queue,
- AdminCP - Email Users
- Spam Cleaner
- Email Confirmation
- User Moderation - Approval
- User Moderation -Rejection
- Password Reset Request
- Password Reset
- Contact Us

Additionally, any addons which use XenForo's built in Mail object should also be captured.

Adds the following options under "Email Options"
 
Enhances the XenForo Email queue with re-try logic and ensures both the front-end and AdminCP both send via the queue.
