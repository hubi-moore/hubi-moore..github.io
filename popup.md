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
                } else {
                    // Show the popup on subsequent visits
                    localStorage.removeItem('popupVisible');
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
