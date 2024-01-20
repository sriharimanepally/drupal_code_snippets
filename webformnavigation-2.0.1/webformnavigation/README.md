# Webform Navigation

## Table of contents

- Introduction
- Requirements
- Installation
- Configuration
- Troubleshooting
- FAQ
- Maintainers

## Introduction
This module creates a navigation setting for webform that allows users to 
navigate forwards and backwards through wizard pages when the wizard progress 
bar is enabled. It performs and logs the validation when a user navigates away 
from a page. Then displays any errors on a page when the user navigates back 
to it.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/webformnavigation).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/webformnavigation).

## Requirements

This module requires the following modules:

- [Webform](https://www.drupal.org/project/webform)

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

### Configuration

1. Enable the module at Administration > Extend.
2. Go to your webform's settings page
   `/admin/structure/webform/manage/webform_machine_name/settings` and scroll to
   the "Third Party Settings" section.
3. To allow for forward navigation,
   simply check the checkbox labeled "Allow forward navigation when the wizard
   progress bar is enabled" checkbox.
4. To allow for bypassing validation when a user presses the "Next" button 
   check the "Prevent validation when the user presses the "Next Page" button" 
   checkbox.
5. Insure the navigation progress bar is
   enabled by visiting
   `/admin/structure/webform/manage/webform_machine_name/settings/form` and
   checking the "Show wizard progress bar" checkbox.
6. Enable the webform navigation handler in the Emails / Handlers tab:
   `/admin/structure/webform/manage/webform_machine_name/settings/handlers`

## Troubleshooting

If the navigation does not display, check the following:

- Is the "Allow forward navigation when the wizard progress bar is enabled" 
  checkbox under "Third Party Settings" checked on the webform's settings form?
- Is the "Show wizard progress bar" checkbox checked in the webform's settings?
- Is the webform navigation handler enabled on the webform? 


## FAQ

**Q: Is there a limit of Wizard Pages in Navigation?.**

**A:** Not that we are aware of.

## Maintainers

- Ryan McVeigh - [rymcveigh](https://www.drupal.org/u/rymcveigh)
