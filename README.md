# DVA

Domain Value Appraisal (域名估值)

### Where does value come from?

DVA FOR `GoDaddy`, `wanMi`, 'yuMi'

### Use 

```php
require_once 'DvaService.php';
```

```php
$dva = new DvaService('abc.com');
$dva->getprice();
```

### test

```bash
php test/ExampleTest.php
```

![效果图](img/example.png)
