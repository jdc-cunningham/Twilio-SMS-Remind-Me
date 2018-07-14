# Twilio-SMS-Remind-Me
Use Twilio to sendt text messages to yourself in the future as reminders


## This is based on Twilio's PHP SDK, you'll need to download that
[Twilio PHP SDK Repo Link](https://github.com/twilio/twilio-php/)
[Direct download link](https://github.com/twilio/twilio-php/archive/master.zip)
[Twilio SDK Docs](https://www.twilio.com/docs/libraries/php)

## Twilio
Twilio is pretty cool. It's interesting when you see services and you figure out how they may have been made regarding the back end. When you see those promotional things that say "Text this number" how do they do that? Well this is one way.

## Note
Twilio is not free, you have to deposite I think at least $20 but the phone number you "rent?" costs $1.00/mo and for 3 cents, you can send 4 messages(receive/send cost the same) MMS is a little more and not 1-1 in cost like SMS.

## This code
This code takes two forms of commands:

`cmd bitcoin-price`

`Remind me #mins`
`Remind me #hrs`

The first one returns bitcoin's current price. This was the first thing I could think of to make at the time. The next one is what I made recently where it will send you back the same text you send roughly(within a minute or two) the same time as you specify in the future.

## What you need for this code to work
You need a LAMP server running, particularly you need:
* CRON
* MySQL and PHP

I've included the rest of the files needed to get this to work. You also need a Twilio phone number obviously(pay) and then once you're logged into your dashboard, you need to get your API keys.

## About the code
### Assumes working directory for receiving from Twilio's POST event is `/var/www/html/twilio-sms/` and the CRON file is in `/var/www` but it can be wherever, just make sure your absolute paths match.

### Four files
Remember this relies on Twilio's PHP SDK folder, you need that whole thing the Master.zip file
* /var/www/html/twilio-sms/index.php
* /var/www/html/twilio-sms/db-connect.php
* /var/www/cron-sms-reminder.php
* reminders.sql

MySQL Database name is `sms_reminder`, table name is `reminders`

### Where do files go
`index.php` is the main file that Twilio hits, so you put this in your public folder eg. `/var/www/html/twilio-sm/index.php` then in Twilio under the `/phone-numbers/incoming` page you specify the messaging to go to `http://your-server-ip-or-domain.com/twilio-sms` then the `index.php` file will receive that POST request and parse your SMS text provided the Twilio phone number hitting the page matches, the Twilio phone number is sent as `$_POST['From']`.

`cron-sms-reminder.php` can go wherever except a public folder. I put it in /var/www/. You don't want it to go in a folder because people could trigger it if they knew the exact name(provided you aren't listing your directory's contents eg. 403 Forbidden) if you want it to be in a public place, you'd want to either check for a super long random string as a POST or GET parameter or modify the file name to have that super long name and match it with Twilio's message setting.

The `cron-sms-reminder.php` is ran by CRON at whatever interval you want. The CRON entry by going to(from your server's terminal) `crontab -e`. Note it matters who's the CRON owner and what permission your cron file is. I'm using root because I'm dumb(also OVH stock setting not using keys/non sudoers) vs. AWS EC2 that uses non-sudoers/ssh-keys.

The CRON entry would be something like: `* * * * * /usr/bin/php /var/www/cron-sms-reminder.php` that would run the script every minute, every 5 minutes is `*/5 * * * * /usr/bin/php /var/www/cron-sms-reminder.php` look up CRON's scheduler format if you want other ways like some day, some month, etc... Faster than minutely you'd want to use a thread/get your time zones right and account for possible shift in time.

## How does it work?
You send an SMS/MMS to Twilio and their phone number receives that message, then it does something to that message(You decide). In my case Twilio sends the SMS body to my server's IP/domain path. See the photo below, obvious details omitted.

-screenshot of twilio here-

Twilio /console/phone-numbers/incoming

OMG this is probably the hardest part of this entire project haha WTF(where) is the place in Twilio where you specify where Twilio sends the SMS to your server. It's [here](https://www.twilio.com/console/phone-numbers/incoming), no joke took me like 10 minutes to find that. You have to have a number first, but yeah, you can then decide where that SMS/MMS gets pointed to(your server's ip address/domain name) just like how you can do programmable emails with MailGun using the same approach. The SMS body is sent to you in your `$_POST` as `$_POST['Body']` you'll see this in the code.
