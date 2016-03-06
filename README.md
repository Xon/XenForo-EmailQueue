# XenForo-EmailQueue

# Queued Email

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

# Bounce Email handling

Instead of hard disabling the account, this allows options which send emails to be disabled. Such as:
- Forum Watch.
- Thread Watch.
- Conversation emails.
- Changes Default settings to 'watch, no email' for threads.
- Email on Tag (Conversation Improvements add-on).

New "Disable Email on" option (checkbox, default non selected):
- On any soft bounce
- Only on too many soft bounces
- Only on a hard bounce
