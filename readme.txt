=== Glassmorphism Panels - Remote Bookkeeping ===
Contributors: PressMeGPT
Tags: full-site-editing, block-patterns, custom-colors, custom-logo, custom-menu
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Glassmorphism Panels - Remote Bookkeeping is a Full Site Editing (FSE) block theme created with PressMeGPT.com - AI WordPress Theme Generator.

This theme uses the WordPress Site Editor for complete customization of headers, footers, and templates.

== Features ==
* Full Site Editing support
* Block-based header and footer
* Built-in mobile responsive navigation
* Custom color palette from AI design
* Google Fonts integration
* Header CTA buttons with layout-aware positioning

== Installation ==
1. Upload the theme folder to /wp-content/themes/
2. Activate the theme through the Themes menu
3. Go to Appearance > Editor to customize templates

== How to Set Call to Action Buttons in the Menu ==

This theme includes two ways to add CTA buttons to your header:

**Option A: Dedicated Header Buttons (Recommended)**

If your design includes CTA buttons, they appear as a separate button group
in the header, next to the navigation menu. To edit these:

1. Go to Appearance > Editor > Patterns > Header
2. Find the "Buttons" block next to the Navigation
3. Click to edit button text, links, and styles
4. Use "Fill" style for primary CTA, "Outline" for secondary

To add new header buttons:
1. Go to Appearance > Editor > Patterns > Header
2. Click the + icon to add a new block
3. Search for "Buttons" and add it
4. Position it next to the Navigation block
5. Configure your button text, link, and style

**Option B: Navigation Menu Items as Buttons**

You can also style any navigation menu item as a button using CSS classes:

1. Go to Appearance > Editor > Patterns > Header
2. Click on the Navigation block to select it
3. Click on the specific menu item you want to style as a button
4. In the right sidebar, expand "Advanced" settings
5. In the "Additional CSS class(es)" field, add one of these classes:
   - nav-button-primary - Solid button with your theme's primary color
   - nav-button-secondary - Outlined button with transparent background
6. Save your changes

**Example Use Cases:**
- Add nav-button-primary to a "Get Started" or "Sign Up" menu item
- Add nav-button-secondary to a "Contact" or "Learn More" menu item
- Combine both for a dual-CTA header

**Tip:** These classes automatically use your theme's button colors,
so they'll match your overall design.

== Changelog ==
= 1.0.0 =
* Initial release
