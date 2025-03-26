# 12GM Learning Management System

A WordPress plugin for creating and managing online courses with WooCommerce integration.

## Features

- **Course Management**: Create and organize courses with lessons
- **Gutenberg Support**: Use WordPress block editor to create rich content
- **Student Dashboard**: Students can view and access their enrolled courses
- **Progress Tracking**: Track and display student progress through courses
- **WooCommerce Integration**: Sell courses as products
- **Student Access Management**: Grant or revoke access to courses
- **Responsive Design**: Works well on all devices
- **Lesson Navigation**: Next/previous buttons and course outline

## Requirements

- WordPress 5.8+
- PHP 7.4+
- WooCommerce 5.0+ (for selling courses)

## Installation

1. Upload the `12gm_lms` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the LMS Dashboard in the admin area to start creating courses

## Usage

### For Administrators

1. **Create Courses**: Go to LMS > Add New Course to create a new course
2. **Create Lessons**: Go to LMS > Add New Lesson to create lessons
3. **Assign Lessons to Courses**: Use the "Assign to Course" meta box when editing a lesson
4. **Link Courses to Products**: Edit a WooCommerce product and select courses in the "LMS Course Access" meta box
5. **Manage Student Access**: Go to LMS > Student Access to manually grant or revoke course access

### For Students

1. **Access Dashboard**: Visit the My Learning Dashboard page to see enrolled courses
2. **Admin Bar Menu**: Use the "My Courses" dropdown in the WordPress admin bar for quick access to courses
3. **View Courses**: Click on a course to view its contents and lessons
4. **Complete Lessons**: Navigate through lessons and mark them as complete
5. **Track Progress**: View completion status for each course

## Shortcodes

- `[12gm_lms_dashboard]` - Displays the student dashboard showing enrolled courses
- `[12gm_lms_course id="123"]` - Displays a specific course's content
- `[12gm_lms_lesson id="456"]` - Displays a specific lesson's content

## Customization

### Templates

The plugin includes these template files that can be overridden in your theme:

- `templates/public/dashboard.php` - Student dashboard displaying all enrolled courses
- `templates/public/course-content.php` - Single course view with lesson list
- `templates/public/lesson-content.php` - Single lesson view with content and navigation
- `templates/public/single-course.php` - Course template wrapper
- `templates/public/single-lesson.php` - Lesson template wrapper

To override a template:

1. Create a folder called `12gm-lms` in your theme directory
2. Copy the template file you want to customize from the plugin's `templates/public/` folder
3. Paste it into your theme's `12gm-lms/` folder
4. Make your customizations to the copied file

Example:
```
your-theme/
├── 12gm-lms/
│   ├── dashboard.php
│   ├── course-content.php
│   └── lesson-content.php
```

### CSS Styling

The plugin includes comprehensive CSS styles that can be customized in several ways:

1. **Theme Compatibility Classes**: The plugin adds standard theme classes like `entry-content`, `site-main`, and `entry-title` to help the plugin content match your theme's styling.

2. **Override Plugin CSS**: Add custom CSS to your theme to override the plugin's styles.

3. **Body Classes**: The plugin adds body classes like `12gm-lms-course-page` and `12gm-lms-lesson-page` to help you target specific page types.

4. **Custom Image Sizes**: The plugin registers custom image sizes for courses (600x400px) and lessons (300x200px).

Example CSS customization in your theme:

```css
/* Style course cards */
.12gm-lms-course-card {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Customize progress bars */
.12gm-lms-progress-fill {
    background-color: #4CAF50; /* Green progress bar */
}

/* Style buttons */
.12gm-lms-course-button {
    background-color: #2196F3;
    font-weight: bold;
}

/* Customize lesson list */
.12gm-lms-lesson-item.is-completed {
    background-color: #f9fff9;
}
```

### Translations

The plugin is fully translation-ready. To translate it into your language:

1. Use a tool like Poedit to open the `languages/12gm-lms.pot` file
2. Create translations for your language
3. Save the resulting `.po` and `.mo` files in the `languages/` folder with the appropriate locale codes (e.g., `12gm-lms-fr_FR.po` and `12gm-lms-fr_FR.mo` for French)

The plugin will automatically load translations based on the site's language setting.

## License

This plugin is licensed under the GPL v2 or later.

## Troubleshooting

### 404 Errors When Accessing Courses or Lessons
If you experience 404 "Page Not Found" errors when trying to access courses or lessons, try these steps:

1. Go to the LMS Dashboard in your WordPress admin area
2. Click the "Flush Permalinks" button in the Tools section
3. If that doesn't resolve the issue, go to Settings > Permalinks and click "Save Changes"

This usually happens after activating the plugin or when you've made changes to permalink structures.

## Support

For support requests, please contact 12GM Support.