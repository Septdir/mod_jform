# JForm Module
Form module using Form Class for the Joomla CMS.

**[Download last version](https://github.com/Septdir/mod_jform/releases/latest)**   

**Supported Joomla versions:** 3.8.0 and later.  
**Read this in other languages:** 
[English](https://github.com/Septdir/mod_jform/blob/master/README.md), 
[Русский](https://github.com/Septdir/mod_jform/blob/master/README.ru-RU.md).


## Module settings
* **Form** - Select the form file from the `modules/mod_jform/forms` directory
* **Handler** - Path to handler  
*Leave the field blank to use the standard com_ajax handler*
* **Return** - Link to return to a specific page  
*Drop the field blank to return to the current page*
* **Use Ajax** - Use AJAX send form to use the form should have the attribute `data-mod_jform="ajax"`
* **Сaptcha** - Add captcha to form
* **Send email** - Activate send email function
* **Administrator email** - The email address where the letter with the form data will come  
*Leave the field blank to use site email*


## How to use
Load the `.xml` form file with [Joomla! Fields](https://docs.joomla.org/Form_field) into the `modules/mod_jform/forms` directory and use.


## Screenshots
### Module settings
![Module settings](https://septdir.ru/images/blog/41/params-en.jpg)
