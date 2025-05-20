# TrainTrack - Student Training and Event Management System

TrainTrack is a comprehensive web application designed to manage student training programs, resume submissions, events, and meetings. The system provides separate interfaces for administrators and students, each with their own set of features and capabilities.

## Features

### Student Features

1. **Account Management**
   - Student registration with unique student ID
   - Secure login with password hashing
   - Profile management
   - Remember me functionality

2. **Resume Management**
   - Upload resumes in PDF or DOCX format
   - View upload history
   - Track resume status (pending, approved, rejected)
   - View admin feedback and remarks
   - Download previously uploaded resumes

3. **Event Management**
   - View upcoming events
   - Register for events
   - Receive unique ticket numbers for registrations
   - Download/print event tickets with QR codes
   - View registered events
   - Track event attendance status

4. **Meeting Management**
   - View scheduled meetings/interviews
   - Access meeting details including:
     - Date and time
     - Meeting type (interview/consultation)
     - Platform (Zoom, Google Meet, Microsoft Teams, In-person)
     - Meeting links
     - Additional notes
   - Track meeting status (scheduled, completed, cancelled)

### Admin Features

1. **Account Management**
   - Admin registration and login
   - Profile management with profile picture
   - Secure authentication system

2. **Resume Management**
   - View all submitted resumes
   - Approve or reject resumes
   - Provide feedback and remarks
   - Delete resume submissions
   - Search resumes by file name or student ID

3. **Event Management**
   - Create new events with:
     - Title and description
     - Date and time
     - Venue
     - Maximum participants
   - Manage event status (upcoming, ongoing, completed, cancelled)
   - View event participants
   - Track registration numbers
   - Delete events

4. **Meeting Scheduling**
   - Schedule meetings after resume approval
   - Set meeting details:
     - Date and time
     - Meeting type (interview/consultation)
     - Platform selection
     - Meeting links
     - Additional notes
   - Provide feedback during approval process
   - Track meeting status

5. **User Management**
   - View all registered students
   - Manage student accounts
   - Track student activities

## Technical Features

1. **Security**
   - Password hashing
   - Session management
   - SQL injection prevention
   - XSS protection
   - Input validation

2. **Database**
   - MySQL database
   - Relational tables with proper constraints
   - Foreign key relationships
   - Indexed fields for better performance

3. **User Interface**
   - Responsive design
   - Modern and clean interface
   - Intuitive navigation
   - Mobile-friendly layout

4. **File Management**
   - Secure file uploads
   - File type validation
   - Unique file naming
   - Organized file storage

## Installation

1. **Requirements**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Web server (Apache/Nginx)
   - Composer (for PDF generation)

2. **Setup**
   - Clone the repository
   - Import `traintrack.sql` to your MySQL database
   - Configure database connection in `config.php`
   - Set up web server to point to the project directory
   - Run `composer install` to install dependencies

3. **Configuration**
   - Update database credentials in `config.php`
   - Ensure proper permissions for uploads directory
   - Configure web server settings if needed

## Directory Structure

```
traintrack/
├── admin/              # Admin interface files
├── includes/           # Common includes
├── uploads/           # File uploads directory
├── config.php         # Database configuration
├── index.php          # Main entry point
├── README.md          # This file
└── traintrack.sql     # Database schema
```

## Usage

1. **Student Access**
   - Register with student ID and email
   - Login to access dashboard
   - Upload resume and track status
   - Register for events
   - View scheduled meetings

2. **Admin Access**
   - Login to admin panel
   - Manage resumes and provide feedback
   - Create and manage events
   - Schedule meetings with students
   - Monitor system activity

## Contributing

Feel free to submit issues and enhancement requests!

## License

This project is licensed under the MIT License - see the LICENSE file for details. 