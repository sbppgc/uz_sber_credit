# uz_sber_credit
Sberbank credits for Amiro.CMS

Copyright Ugol zreniya. All rights reserved.

Author Sergey Prisyazhnyuk <sbpmail@ya.ru>

License: MIT; see LICENSE.txt

Install notes see in docs/install.txt

How it works:

You generate payment link and send to user (usually, in leter when set specific order status).
User click the link, open script on your site.
Script do register order in Sberbank by API and redirect user to sberbank site.
User fill the credit form and submit.

How it was intended, after submit credit form, we expect return user back, on special return URL (API provides for the appropriate fields).
But it's not works.
So, at this moment, needs to process orders into Sberbank private office. And manually set orders statuses on site.
