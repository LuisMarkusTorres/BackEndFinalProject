# BackEndFinalProject
A web application for logging and analyzing driving experiences with detailed statistics and visual analytics. Built as part of a comprehensive web development course, this project demonstrates modern frontend and backend integration with secure database management.

## Overview

The Driving Experience Tracker allows users to record detailed information about their driving sessions including date, time, distance covered, weather conditions, traffic levels, road conditions, and specific maneuvers performed. The application provides comprehensive statistical analysis through interactive charts and filterable tables, making it easy to track driving progress over time.

## Features

* Complete CRUD Operations
   * Create new driving experience entries with comprehensive details
   * Read and view all recorded experiences in an organized table format
   * Update existing entries to correct or add information
   * Delete unwanted records with confirmation dialogs

* Advanced Data Visualization
   * Interactive doughnut charts displaying condition distributions
   * Bar charts for maneuver frequency analysis
   * Tabbed interface organizing statistics by category
   * Accordion sections for space-efficient data presentation

* Professional Table Management
   * DataTables integration with sorting and pagination
   * Real-time search across all table columns
   * Adjustable rows per page display
   * Date range filtering with calendar widgets

* Mobile-First Design
   * Fully responsive layout adapting to all screen sizes
   * Touch-friendly interface elements
   * Readable text sizing on small screens
   * Horizontal scrolling for data tables on mobile devices

## Technical Implementation

* Frontend Technologies
   * HTML5 with semantic markup structure
   * CSS3 featuring Grid and Flexbox layouts
   * JavaScript ES6+ for modern functionality
   * jQuery 3.7.0 for DOM manipulation
   * jQuery UI 1.13.2 providing tabs, accordions, dialogs, and datepickers
   * Chart.js for data visualization
   * DataTables 1.13.7 for table enhancement

* Backend Architecture
   * PHP 8+ handling server-side logic
   * PDO for database connectivity with prepared statements
   * MySQL storing relational data
   * Session management for security
   * Object-oriented programming structure

* Security Measures
   * PDO prepared statements preventing SQL injection
   * Parameter binding for all database queries
   * Session-based anonymous code generation
   * Server-side input validation and sanitization
   * Secure identifier mapping protecting database keys

## Database Design

The application uses a normalized relational database structure. The main driving_experiences table stores core session data while separate lookup tables maintain weather conditions, traffic levels, road slipperiness states, and light conditions. This structure ensures data integrity and reduces redundancy through the use of foreign key relationships.

## Code Structure

The project follows a clean separation of concerns with dedicated files for different functionality. Database connection is handled through a custom PDO wrapper class, while driving experience operations are managed through an object-oriented class structure. The frontend JavaScript is modular with separate chart rendering functions and event handlers. CSS styling is organized with CSS custom properties for consistent theming throughout the application.

## Form Design

The main entry form uses extensive HTML5 validation attributes including required fields, number input types, and placeholder text. Labels are properly associated with their corresponding input fields using the for attribute, improving accessibility and user experience. All form controls maintain readable font sizes across devices, with particular attention to mobile rendering where text in select lists and input fields remains clearly legible.

## Statistics Dashboard

The statistics page features a tabbed interface separating driving conditions from maneuver analysis. Within the conditions tab, an accordion organizes different environmental factors into collapsible sections. Charts render dynamically based on aggregated data from all recorded experiences. A date range filter allows users to focus on specific time periods, with the filtering integrated seamlessly into the DataTables display.# BackEndFinalProject
