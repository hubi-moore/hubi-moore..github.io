To create a popup that is displayed on every page in Magento 2.4.x, you can follow these steps:

Step 1: Create a Module
Create a custom module in your Magento 2.4.x installation. You can name it something like "Custom_Popup."

Step 2: Create the Popup Template
Create a popup template file in your module's directory. For example, create a file named `popup.phtml` in the following location: `app/code/Custom/Popup/view/frontend/templates/popup.phtml`

In this template file, you can add your popup content, including the checkbox for the user to indicate if they want it to disappear forever. Here's an example of the template code:

```html
<div id="popup">
    <!-- Add your popup content here -->
    <input type="checkbox" id="popup-checkbox" />
    <label for="popup-checkbox">Don't show this popup again</label>
</div>

<script type="text/javascript">
    require(['jquery'], function($) {
        $(document).ready(function() {
            // Check if the popup should be shown or hidden based on the checkbox state
            var showPopup = localStorage.getItem('popupVisible');
            if (!showPopup) {
                $('#popup').show();
            }

            // Handle checkbox change event
            $('#popup-checkbox').change(function() {
                if ($(this).is(':checked')) {
                    // Hide the popup permanently
                    localStorage.setItem('popupVisible', false);
                    $('#popup').hide();
                } else {
                    // Show the popup on subsequent visits
                    localStorage.removeItem('popupVisible');
                    $('#popup').show();
                }
            });
        });
    });
</script>
```

Step 3: Create a Layout XML File
Create a layout XML file in your module's directory. For example, create a file named `default.xml` in the following location: `app/code/Custom/Popup/view/frontend/layout/default.xml`

In this layout file, you need to add a block that references the popup template you created. Here's an example of the layout XML code:

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="after.body.start">
            <block class="Magento\Framework\View\Element\Template" name="custom.popup" template="Custom_Popup::popup.phtml" />
        </referenceContainer>
    </body>
</page>
```

Step 4: Enable the Module
Enable your custom module by running the following commands in the Magento root directory:

```
php bin/magento module:enable Custom_Popup
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
```

Step 5: Flush Cache
Flush the Magento cache by running the following command:

```
php bin/magento cache:flush
```

After completing these steps, your popup should be displayed on every page of your Magento 2.4.x store. The checkbox will allow users to indicate if they want the popup to disappear forever, and their preference will be stored using local storage.
<br/>
<br/>
CSS:<br/>
Here's an example of CSS code that you can use for styling your popup:

```css
#popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: none;
    max-width: 400px;
}

#popup-checkbox {
    margin-right: 5px;
}

#popup label {
    font-size: 14px;
}

#popup-close {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    color: #999;
    font-size: 20px;
}
```

This CSS code provides a basic styling for the popup element and checkbox. You can customize the styles further based on your requirements.

To use this CSS, you can either include it in your theme's CSS file or add it to a custom CSS file and include it in your theme.

Make sure to adjust the CSS selectors based on your HTML structure and classes used in your popup template.

<br/>
<br/>
NOW UPDATED HTML:<br/>
Here's an updated version of the HTML popup code with additional elements and styling:

```html
<div id="popup">
    <div id="popup-content">
        <h2>Welcome to Our Website!</h2>
        <p>Thank you for visiting our website. Sign up for our newsletter to receive the latest updates and exclusive offers.</p>
        <form id="popup-form" action="#">
            <input type="email" id="popup-email" placeholder="Enter your email" required />
            <button type="submit">Sign Up</button>
        </form>
        <div id="popup-checkbox">
            <input type="checkbox" id="popup-checkbox-input" />
            <label for="popup-checkbox-input">Don't show this popup again</label>
        </div>
        <span id="popup-close">&times;</span>
    </div>
</div>
```

This HTML code includes a heading, paragraph, email input field, submit button, and a close button. The checkbox and label are wrapped in a `div` element for proper styling.

Please note that this code is a basic example, and you can modify it according to your specific requirements.

Remember to update the CSS and JavaScript code accordingly if you make any changes to the HTML structure.
