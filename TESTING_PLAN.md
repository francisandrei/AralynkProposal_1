# Aralynk Testing Plan (Laravel version)

Run `php artisan migrate:fresh --seed` and `php artisan db:seed --class=DemoSeeder` first. Mark each case Pass or Fail.

## 1. Authentication
| # | Test | Expected |
|---|---|---|
| A1 | Register a student with valid data | Account created, sent to login |
| A2 | Register again with the same email or student ID | Error message |
| A3 | Password `abc` | Error: at least 8 characters with letters and numbers |
| A4 | Register a business, then try to log in | "Waiting for admin approval" |
| A5 | Admin approves the business, then it logs in | Business dashboard opens |
| A6 | Log in as student, business and admin | Each reaches its own dashboard |
| A7 | Wrong password | "Wrong email or password" |
| A8 | 5 wrong passwords | Locked for 5 minutes |
| A9 | Log out, press Back | Cannot see private pages |
| A10 | Student opens `/admin/dashboard` or `/business/dashboard` | "Access denied" (403) |
| A11 | Not logged in, open `/student/dashboard` | Sent to login |
| A12 | Admin deactivates a student who is logged in, student clicks a link | Logged out with "not active" |

## 2. Student
| # | Test | Expected |
|---|---|---|
| S1 | Edit basic information | Saved, completion bar changes |
| S2 | Tick skills, add "other skills" `Welding, Sewing` | Saved, new skills appear in lists |
| S3 | Add and remove experience | Appears and disappears |
| S4 | Weekly availability, end time before start time | Error, nothing saved |
| S5 | "Available for this day": future date | Appears in the list |
| S6 | "Available for this day": past date | Error |
| S7 | Search by keyword, category, type, skill, place, day, date; sort | Results follow the filters |
| S8 | Apply with a message | Shown as Pending, notifications created |
| S9 | Apply again to the same job | "You already applied" |
| S10 | Apply after the deadline, or to a closed job | Refused |
| S11 | Withdraw, then apply again | Works |
| S12 | "I'm available for this day" on a dated job | Availability chip improves |
| S13 | Report a job post | Admin sees it under Jobs |

## 3. Business
| # | Test | Expected |
|---|---|---|
| B1 | Edit business profile | Saved |
| B2 | Post a job of each type | Appears in My jobs |
| B3 | 1-Day job without a date | Error |
| B4 | Several-Day end date before start date | Error |
| B5 | Recurring job without weekdays | Error |
| B6 | Deadline after the work date | Error |
| B7 | Edit, close, reopen, delete a job | Status changes |
| B8 | Open another business's job by typing its number in the address | "Not found" |
| B9 | Applicants list default order | Oldest application first |
| B10 | Sort by match factors | Reorders, labeled "guide only" |
| B11 | Applicant profile before accepting | Contact details hidden |
| B12 | Accept an applicant | Contact details shown, student notified |
| B13 | Reject, then mark as reviewed again | Allowed |
| B14 | Message a student who did not apply | Not allowed |

## 4. Ratings (new)
| # | Test | Expected |
|---|---|---|
| R1 | Rate before the job is Completed | No form is shown |
| R2 | Business presses "Mark as completed" on a job | Accepted students become Completed |
| R3 | Business rates a Completed student, 1 to 5 stars with a comment | Saved, student notified |
| R4 | Student rates the business for the same application | Saved, business notified |
| R5 | Try to rate the same application twice | "You already rated this job" |
| R6 | Stars 0 or 6 (edit the form in the browser) | Validation error |
| R7 | A different student or business posts to `/applications/{id}/rate` | 403 |
| R8 | Averages | Shown on dashboards, profiles and applicant cards, and match the data |
| R9 | Admin removes a rating | It disappears and the average updates |

## 5. Messages and notifications
| # | Test | Expected |
|---|---|---|
| M1 | Student messages a business from a job page | Appears in the business inbox with an unread badge |
| M2 | Business replies | Student sees it, "read" shows after opening |
| M3 | Opening the conversation | Unread badge clears |
| N1 | Apply, status change, rating, new message, new matching job | A notification for each |
| N2 | Open Alerts | New ones in bold, then marked read |

## 6. Admin
| # | Test | Expected |
|---|---|---|
| D1 | Dashboard numbers | Match the database |
| D2 | Approve and reject a business | Status changes |
| D3 | Deactivate and activate students and businesses | Login blocked or allowed |
| D4 | Hide a reported job | Students cannot see it, owner notified |
| D5 | Categories: add, rename, deactivate, delete (used category) | Used category cannot be deleted |
| D6 | Skills: add and delete | Removed from students and jobs |
| D7 | Applications page | No accept or reject buttons |
| D8 | Reports | Cards and both charts render and match the data |

## 7. Matching
| # | Test | Expected |
|---|---|---|
| M4 | Job needs 4 skills, student has 3 | Skills 30 of 40, label Partial |
| M5 | Job needs no skills | Skills 40 of 40 |
| M6 | Related course, no experience | Background 30 of 30 |
| M7 | Related experience, different course | Background 30 of 30 (same) |
| M8 | Job Monday 2 PM to 5 PM, student free Monday 1 PM to 6 PM | Availability 30 of 30 |
| M9 | Student free only part of the shift | Half points for that day |
| M10 | Date marked "Available for this day" | Counts for that date |
| M11 | Low score student applies | Application accepted, never auto-rejected |

## 8. Security checks
- Type `<script>alert(1)</script>` in a job title, "about me", a comment and a message: it shows as text and never runs (Blade escapes output).
- Type `' OR 1=1 --` in the login email and search boxes: no error and no leaked data (Eloquent uses prepared statements).
- Remove the `_token` field with the browser tools and submit a form: Laravel shows "Page Expired" (CSRF protection).
- In phpMyAdmin, `users.password` holds hashes starting with `$2y$`, never plain text.
