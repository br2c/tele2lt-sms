# tele2lt-sms-api

Mobiliojo ryšio operatorius TELE2 savo klientams suteikia galimybę siųsti SMS žinutes iš savitarnos svetainės
[mano.tele2.lt](https://mano.tele2.lt). Pasinaudojus svetainėje esančia forma galima siųsti
ribotą skaičių nemokamų SMS žinučių ir neribotą skaičių žinučių, už kurias reikia mokėti.
Išnaudojus nemokamų SMS žinučių limitą kitos žinutės bus apmokestinamos ir įtrauktos į bendrą mėnesinę sąskaitą
už mobiliojo ryšio paslaugas. Daugiau informacijos rasite prisijungę prie savitarnos svetainės.

Ši PHP biblioteka (SMS API) skirta siųsti SMS žinutes per [mano.tele2.lt](https://mano.tele2.lt).

Biblioteka gali būti instaliuojama naudojant `composer` į savo projekto `composer.json`
failą pridedant tokią konfigūraciją:
```
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/br2c/tele2lt-sms-api.git"
        }
    ],
    "require": {
        "br2c/tele2lt-sms-api": "dev-master"
    }
}
```

Pavyzdys, skirtas išsiųsti SMS žinutę ir atspausdinti sąskaitos informaciją:
```
use Tele2LtSms\Client\Adapter;
use Tele2LtSms\Client\Login;
use Tele2LtSms\Client\Action;

// Telefono numeris, naudojamas prisijungimui prie mano.tele2.lt; 6XXYYYYY formatas
$username = '';
// Slaptažodis, naudojamas prisijungimui prie mano.tele2.lt
$password = '';
// Gavėjo telefono numeris; 6XXYYYYY formatas
$receiver = '';
// SMS žinutės tekstas
$message = '';

$adapter = new Adapter();

$login = new Login($adapter);
$action = new Action($adapter);

$session = $login->createSession($username, $password);

// Siųsti SMS žinutę
$action->sendSms($session, $receiver, $message);

$account = $action->getAccount($session);

// Atvaizduoti sąskaitos informaciją
echo "Liko išsiųsti nemokamų SMS: ";
echo $account->getFreeSmsCount();

echo "\n";

echo "Vienos mokamos SMS žinutės kaina: ";
echo $account->getSmsCharge();

echo "\n";
```
