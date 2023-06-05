ZANIM ZACZNIESZ.
Do bazy danych powinny trafić dwa nowe pola - na pewno do sales_flat_order (?) ale czy tez do quote to nie wiem
 - `termin_realizacji`: varchar(255) DEFAULT 'Natychmiastowa',
 - `prods_dostawa_zew`: varchar(255) DEFAULT 'NIE',

```xml
WORKTREE:
  └── webroot/
      ├── app/
      │   ├── code/
      │   │   └── local/
      │   │       ├── Network/
      │   │       │   ├── ProductSubstitutes/
      │   │       │   │   └── controllers/
      │   │       │   │       ├── DostawazewController.php
      │   │       │   │       └── IndexController.php
      │   │       │   └── Sap/
      │   │       │       ├── controllers/
      │   │       │       │   └── IndexController.php
      │   │       │       ├── etc/
      │   │       │       │   └── config.xml
      │   │       │       └── Model/
      │   │       │           └── Observer.php
      │   │       └── Triton/
      │   │           └── Sales/
      │   │               └── Block/
      │   │                   └── Adminhtml/
      │   │                       └── Sales/
      │   │                           └── Order/
      │   │                               ├── Renderer/
      │   │                               │   ├── ProdsDostawaZew.php
      │   │                               │   └── TerminRealizacji.php
      │   │                               └── Grid.php
      │   └── design/
      │       ├── adminhtml/
      │       │   └── default/
      │       │       └── default/
      │       │           └── template/
      │       │               └── sales/
      │       │                   └── order/
      │       │                       └── view/
      │       │                           └── info.phtml
      │       └── frontend/
      │           └── athlete/
      │               └── dark_us/
      │                   ├── layout/
      │                   │   └── amasty/
      │                   │       └── amscheckout/
      │                   │           └── main.xml
      │                   └── template/
      │                       ├── amasty/
      │                       │   └── amscheckout/
      │                       │       ├── onepage/
      │                       │       │   └── shipping_custom_date.phtml
      │                       │       └── onepage.phtml
      │                       └── catalog/
      │                       │   └── product/
      │                       │       └── view/
      │                       │           └── type/
      │                       │               └── grouped.phtml
      │                       └── checkout/
      │                           ├── cart/
      │                           │   └── item/
      │                           │       └── default.phtml
      │                           └── cart.phtml
      └── skin/
          └── frontend/
              └── athlete/
                  └── dark_us/
                      └── js/
                          └── checkout-date-picker.js
```
