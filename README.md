Sms authrizition + laravell passport
<div dir="rtl">

# احراز هویت و ثبت نام پیامکی کاربر با شماره موبایل، ساخت توکن توسط لاراول پاسپورت
<p> راهنما</p>


من از سامانه پیامکی ایده پردازان sms.ir استفاده کرده ام\
برای استفاده از سامانه های دیگر تنها کافی است تابع sendSms را در AuthController تغییر بدهید.

- "composer update"
- rename .env.example to .env
- set your database name in .env file
- "php artisan key:generate" 
- "php artisan migrate" 
- "php artisan passport:install"
- set your service data in AuthController\sendSms
- "php artisan serv"


laravel version 8.0
</div>
