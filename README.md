# An Intelligent Alumni Association Platform for Smart Educational Institutions

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![React Native](https://img.shields.io/badge/React_Native-20232A?style=for-the-badge&logo=react&logoColor=61DAFB)
![WebRTC](https://img.shields.io/badge/WebRTC-333333?style=for-the-badge&logo=webrtc&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A full-stack web-based Alumni Association Platform designed for educational institutions to connect students and alumni through networking, events, communication, and centralized administration.

</div>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [System Architecture](#system-architecture)
- [Getting Started](#getting-started)
- [Default Accounts](#default-accounts)
- [Mobile Application](#mobile-application)
- [Key Pages & Routes](#key-pages--routes)
- [Contributing](#contributing)

---

## Overview

The **Alumni Association Platform** provides a centralized digital environment for managing alumni information, organizing events, publishing announcements, facilitating peer-to-peer communication, and strengthening connections between current students and alumni of educational institutions.

The system offers two distinct interfaces:
- **User Portal** — for alumni to network, register for events, browse ads, send messages, and make video calls
- **Admin Dashboard** — for administrators to manage users, events, news, feedback, and all platform activities

---

## Features

### User Registration & Authentication
- Secure user registration with email-based accounts
- BCrypt password hashing via PHP's `password_hash()`
- Role-based access control (`admin` / `user`)
- Session-based login with account approval workflow (`Pending` → `Approved` / `Rejected`)
- Automatic redirects based on role on login

### Alumni Networking
- Browse and discover alumni profiles
- View detailed alumni profiles (profile picture, job position, company, qualification, batch year, location)
- Upload and view resumes
- Online presence tracking via `last_activity` timestamp

### Event Management
- Create and manage alumni events
- View upcoming events with photos, location, and descriptions
- Register/unregister for events
- Admin tools for adding, editing, and deleting events
- Separate tracking for `Event` type and `News` type entries

### News & Announcements
- Publish institutional news and announcements
- View and manage news articles
- Distinct display for news vs events

### Messaging System
- One-on-one real-time chat between alumni
- Chat invitation system with `pending` / `accepted` / `declined` states
- Message read/unread tracking
- File sharing support in messages
- Conversation management with timestamps

### Video Calling (WebRTC)
- Browser-based peer-to-peer video calling using **WebRTC**
- PHP-based signaling server (`signaling_server.php`)
- Call invitation and acceptance flow
- Mute/unmute microphone and toggle video
- Admin dashboard for monitoring active/past calls
- Room ID-based call sessions

### Notifications System
- Unified notification center
- Notification types: `message`, `call`, `chat_invitation`, `event`, `advertisement`
- Mark as read / bulk mark as read
- Real-time notification polling

### Advertisement Board
- Alumni can post advertisements with category, status, and photo
- Application-based advertisements where users can apply
- Admin tools for managing advertisement visibility and status
- Categories include: Engineering, IT, Business, Design, and more

### Feedback System
- Users can submit feedback about the platform
- Admin can view, manage, and export feedback
- Detailed feedback view for administrators

### Admin Dashboard
Administrators have full control over:
- **User Management** — approve/reject/manage user accounts
- **Alumni Management** — view and edit all alumni records
- **Event Management** — create, edit, and delete events
- **News Management** — manage announcements
- **Advertisement Management** — oversee all advertisements
- **Feedback Management** — collect and review user feedback
- **Video Call Management** — monitor video call activity
- **Reports & Exports** — export comprehensive data reports (CSV/PDF)
- **Platform Statistics** — real-time dashboard with counts for users, events, news, and more

---

## Tech Stack

### Web Application

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Frontend | HTML5 + CSS3 | Application structure and styling |
| UI Framework | Bootstrap 5.3 | Responsive, mobile-first UI components |
| Icons | Bootstrap Icons | Icon library |
| Interactivity | JavaScript + jQuery | Dynamic UI and AJAX requests |
| Data Tables | DataTables.js | Sortable, searchable admin tables |
| Backend | PHP 8+ | Server-side logic and session management |
| Database | MySQL (via MySQLi) | Relational data storage with `utf8mb4` charset |
| Real-time Video | WebRTC | Peer-to-peer video/audio communication |
| Signaling | PHP (custom) | WebRTC offer/answer/ICE candidate exchange |
| Server | Apache (XAMPP) | Local development server |

### Mobile Application

| Technology | Version | Purpose |
|-----------|---------|---------|
| React Native | 0.72.6 | Cross-platform mobile framework |
| React | 18.2.0 | UI component library |
| TypeScript | 4.8.4 | Type-safe development |
| React Navigation | 6.x | Screen navigation (Stack + Bottom Tabs) |
| Axios | 1.6.0 | API communication with PHP backend |
| AsyncStorage | 1.19.5 | Local token and session persistence |
| React Native Paper | 5.11.1 | Material Design UI components |
| Linear Gradient | 2.8.3 | Visual design gradients |
| Push Notifications | 8.1.1 | Mobile push notification support |

---

## Project Structure

```
Alumni Platform/
├── src/                          # Main PHP web application
│   ├── db_controller.php         # Database connection handler
│   ├── init_db.php               # Database initializer + seed data
│   ├── index.php                 # Landing / entry page
│   ├── login.php                 # Login page
│   ├── registration.php          # User registration
│   ├── logout.php                # Session termination
│   ├── main.php                  # Main portal home
│   ├── main_menu.php             # User dashboard/menu
│   ├── admin_dashboard.php       # Admin dashboard
│   │
│   ├── view_alumni.php           # Browse all alumni
│   ├── profile_detail.php        # Individual alumni profile
│   ├── update_profile.php        # Edit own profile
│   ├── settings.php              # Account settings
│   ├── view_resume.php           # View uploaded resume
│   │
│   ├── view_events.php           # Browse upcoming events
│   ├── view_events_modern.php    # Modern event listing UI
│   ├── add_event.php             # Create a new event (admin)
│   ├── edit_event.php            # Edit an event (admin)
│   ├── manage_events.php         # Event management (admin)
│   ├── view_news_modern.php      # News/announcements listing
│   ├── manage_news.php           # News management (admin)
│   │
│   ├── messages.php              # Chat/messaging interface
│   ├── get_messages.php          # AJAX: fetch messages
│   ├── send_message.php          # AJAX: send message
│   ├── send_file.php             # File sharing in chat
│   ├── get_conversations.php     # List user conversations
│   ├── start_conversation.php    # Initiate new conversation
│   ├── handle_chat_invitation.php# Accept/decline chat invites
│   │
│   ├── videocall.php             # WebRTC video call UI
│   ├── video_calls_dashboard.php # Video call management
│   ├── initiate_call.php         # Start a video call
│   ├── respond_to_call.php       # Accept/decline calls
│   ├── signaling_server.php      # WebRTC signaling backend
│   ├── manage_video_calls.php    # Admin: manage calls
│   │
│   ├── notifications_center.php  # Notification hub
│   ├── get_unified_notifications.php # AJAX: fetch all notifications
│   ├── mark_notifications_read.php   # Mark as read
│   ├── check_notifications.php       # Polling for new notifications
│   │
│   ├── view_advertisements.php   # Browse advertisements
│   ├── add_advertisement.php     # Post an advertisement
│   ├── edit_advertisement.php    # Edit advertisement
│   ├── advertisement_apply.php   # Apply for an advertisement
│   │
│   ├── feedback_form.php         # Submit feedback
│   ├── manage_feedback.php       # Admin: view/manage feedback
│   │
│   ├── manage_users.php          # User account management
│   ├── manage_alumni.php         # Alumni record management
│   ├── reports.php               # Platform reports
│   ├── export_comprehensive.php  # Full data export
│   │
│   ├── init_db.php               # Database setup + seeder
│   └── test_connection.php       # DB connection test
│
├── components/                   # Reusable PHP UI components
│   ├── nav_modern.php            # Modern navigation bar
│   ├── admin_nav.php             # Admin navigation bar
│   ├── admin_footer.php          # Admin footer
│   ├── admin_template.php        # Admin page template
│   └── alumni_logo.php           # Branding/logo component
│
├── css/                          # Stylesheets
│   ├── styles.css                # Global/main stylesheet
│   ├── admin-theme.css           # Admin panel theme
│   └── admin-clean.css           # Clean admin layout styles
│
├── images/                       # Static image assets
│   ├── default_events.jpg
│   ├── default_news.png
│   ├── default_advertisement.jpg
│   ├── profile_picture.jpg
│   ├── main_photo.jpg
│   └── ...
│
├── profile_images/               # User-uploaded profile pictures
├── uploads/                      # User-uploaded files (resumes, etc.)
│
└── AlumniPortalMobileApp/        # React Native mobile application
    ├── package.json
    ├── index.js
    ├── android/
    └── src/
        ├── App.tsx
        ├── context/AuthContext.tsx
        ├── navigation/
        ├── screens/
        │   ├── auth/
        │   │   ├── LoginScreen.tsx
        │   │   ├── RegisterScreen.tsx
        │   │   └── ForgotPasswordScreen.tsx
        │   └── main/
        │       ├── DashboardScreen.tsx
        │       └── EventsScreen.tsx
        └── utils/
            ├── Colors.ts
            └── Constants.ts
```

---

## Database Schema

The platform uses a MySQL database named **`Alumni`** with 12 relational tables:

| Table | Description |
|-------|-------------|
| `user_table` | Alumni profile data (name, DOB, gender, batch year, company, qualification, etc.) |
| `account_table` | Login credentials and account status (`Pending` / `Approved` / `Rejected`) |
| `event_table` | Events and news (title, location, date, photo, type: `Event` or `News`) |
| `event_registration_table` | Tracks which users registered for which events |
| `advertisement_table` | Job/opportunity advertisements posted by users or admin |
| `advertisement_registration_table` | Tracks applications submitted to advertisements |
| `chat_conversations_table` | One-on-one conversation threads between two users |
| `chat_messages_table` | Individual messages within conversations (with read status + file support) |
| `chat_invitations_table` | Chat connection requests (`pending` / `accepted` / `declined`) |
| `notifications_table` | In-app notifications (type: `message`, `call`, `chat_invitation`, `event`, `advertisement`) |
| `video_call_table` | Video call sessions (room ID, caller, callee, status, timestamps) |
| `video_call_notifications_table` | Notifications tied to incoming video call events |

> **Auto-initialization:** Navigate to `init_db.php` in your browser to automatically create the database, all tables, and seed them with sample data.

---

## System Architecture

```
                    ┌─────────────────────────┐
                    │        End Users         │
                    │  Students │ Alumni │ Admin│
                    └────────────┬────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │      Web Interface       │
                    │  Bootstrap 5 │ jQuery    │
                    │  JavaScript  │ HTML/CSS  │
                    └────────────┬────────────┘
                                 │  HTTP / AJAX
                    ┌────────────▼────────────┐
                    │      PHP Backend         │
                    │  Session Auth            │
                    │  User Management         │
                    │  Events / News           │
                    │  Messaging / Ads         │
                    │  WebRTC Signaling        │
                    └────────────┬────────────┘
                                 │  MySQLi
                    ┌────────────▼────────────┐
                    │     MySQL Database       │
                    │  DB: Alumni              │
                    │  12 relational tables    │
                    └─────────────────────────┘

              ┌────────────────────────────────────┐
              │  Mobile Application (Android)       │
              │  React Native 0.72 + TypeScript     │
              │  Axios → PHP API Backend            │
              └────────────────────────────────────┘

              ┌────────────────────────────────────┐
              │  WebRTC Video Calls                 │
              │  Browser ◄──► Browser (P2P)         │
              │  Signaling via signaling_server.php │
              └────────────────────────────────────┘
```

---

## Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL
- PHP 8.0 or higher
- A modern web browser (Chrome, Firefox, Edge)
- Node.js ≥ 16 *(mobile app only)*
- Android Studio *(mobile app only)*

### Installation

**1. Clone the Repository**
```bash
git clone https://github.com/Yukesh-S1/An-Intelligent-Alumni-Association-Platform-for-Smart-Educational-Institutions.git
```

**2. Deploy to XAMPP htdocs**

Copy the `src/` contents into your XAMPP `htdocs` folder under a directory named `new`:
```
C:\xampp\htdocs\new\
```

**3. Start XAMPP Services**

Open XAMPP Control Panel (as Administrator) and start:
- ✅ **Apache** — web server (port 80 or 8080)
- ✅ **MySQL** — database server (port 3306)

**4. Initialize the Database**

Open your browser and navigate to:
```
http://localhost/new/init_db.php
```
This will automatically:
- Create the `Alumni` MySQL database
- Create all 12 relational tables
- Populate with sample users, events, and advertisements

**5. Access the Platform**

| URL | Description |
|-----|-------------|
| `http://localhost/new/index.php` | Landing page |
| `http://localhost/new/login.php` | Login page |
| `http://localhost/new/main_menu.php` | User dashboard |
| `http://localhost/new/admin_dashboard.php` | Admin dashboard |

> If Apache uses port 8080, use `http://localhost:8080/new/...`

**6. Verify Connection** *(optional)*
```
http://localhost/new/test_connection.php
```

---

## Default Accounts

The database is pre-seeded with the following test accounts:

### Admin
| Email | Password |
|-------|----------|
| `admin@swin.edu.my` | `admin` |

### Approved Users
| Email | Password |
|-------|----------|
| `user0@test.com` | `user` |
| `user1@test.com` | `user` |
| `user2@test.com` | `user` |
| `user5@test.com` | `user` |

### Pending / Rejected Users (for testing approval flow)
| Email | Password | Status |
|-------|----------|--------|
| `user8@test.com` | `user` | Pending |
| `user9@test.com` | `user` | Pending |
| `user3@test.com` | `user` | Rejected |

---

## Mobile Application

The `AlumniPortalMobileApp/` directory contains a **React Native (Android)** mobile client.

### Setup & Run

```bash
cd AlumniPortalMobileApp
npm install

# Terminal 1 — Start Metro bundler
npx react-native start

# Terminal 2 — Run on Android
npx react-native run-android
```

### Mobile Screens

| Screen | Description |
|--------|-------------|
| `LoginScreen` | Email/password login with validation |
| `RegisterScreen` | New user account registration |
| `ForgotPasswordScreen` | Password recovery flow |
| `DashboardScreen` | Home with live stats (alumni count, events, news) |
| `EventsScreen` | Browse and filter alumni events |

---

## Key Pages & Routes

| Route | Access | Description |
|-------|--------|-------------|
| `index.php` | Public | Landing page |
| `login.php` | Public | User login |
| `registration.php` | Public | New account registration |
| `main_menu.php` | User | Home dashboard with stats |
| `view_alumni.php` | User | Browse all alumni profiles |
| `profile_detail.php` | User | Individual alumni profile view |
| `update_profile.php` | User | Edit own profile and photo |
| `view_events.php` | User | Browse events with registration |
| `view_advertisements.php` | User | Browse and apply to ads |
| `messages.php` | User | Chat messaging interface |
| `videocall.php` | User | WebRTC peer-to-peer video call |
| `notifications_center.php` | User | All in-app notifications |
| `feedback_form.php` | User | Submit platform feedback |
| `admin_dashboard.php` | Admin | Admin control panel with stats |
| `manage_users.php` | Admin | Approve/reject user accounts |
| `manage_alumni.php` | Admin | Manage alumni records |
| `manage_events.php` | Admin | Create/edit/delete events |
| `manage_news.php` | Admin | Manage news/announcements |
| `manage_feedback.php` | Admin | View and manage all feedback |
| `manage_video_calls.php` | Admin | Monitor video call activity |
| `reports.php` | Admin | Platform activity reports |
| `export_comprehensive.php` | Admin | Full data export |
| `init_db.php` | Setup | Initialize database (run once) |

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Apache won't start | Run `fix_port_conflict.bat` to free port 80/8080 |
| MySQL won't start | Run `fix_mysql.bat`; verify port 3306 is free |
| Database connection error | Ensure MySQL is running in XAMPP; check `db_controller.php` port |
| Pages not loading | Ensure project folder is named `new` inside `htdocs` |
| Database errors | Re-run `http://localhost/new/init_db.php` |
| Video call not connecting | Use localhost (not IP); grant camera/microphone permissions |
| No audio in video call | Check mic is not muted; check system volume |

---

## Contributing

1. Fork this repository
2. Create your feature branch: `git checkout -b feature/YourFeature`
3. Commit your changes: `git commit -m 'Add YourFeature'`
4. Push to the branch: `git push origin feature/YourFeature`
5. Open a Pull Request

---

## License

This project was developed as an academic project for educational institutions.

---

<div align="center">

Made with ❤️ by **Yukesh Sekar**  
*Final-year CSBS Student | AI Engineering & Data Analytics*  
[GitHub](https://github.com/Yukesh-S1) · [LinkedIn](https://www.linkedin.com/in/yukeshsekar/)

</div>
