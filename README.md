# Sierra Online Card Registration

## Presented at OLA 2020

#### Background

This repository was created by Chris Jasztrab and Kanta Kapoor and is the initial code release for the Online Patron Registration module that was created by MPL.  Knowledge of the Sierra API is required and you will need an API key for your ILS to get started.

It is strongly suggested that whomever is implementing this code has a background in PHP as the entire codebase is PHP and although I've done my best to provide comments in the code, there will be some modifications you're going to want to make.  Someone with a good understanding of PHP should be able to take the functions libraries and create their own form to gather the data and post it.

MPL chose to create our own online card registration system so that we could customize the look and feel of the site and add additional branding to the email that is sent to the patron.  Also this doesn't cost us anything other than the time it took to code it.  Bing Maps is one of the external vendors that this system relies on and they provide API access for free to non-profits.  You will need to apply for an API key with Bing in order for this to work.
If you choose to not do all the address checks and don't have a non-resident fee, than you can eliminate a lot of the address checks.  

**Sierra Online Library Card Registration WebApp**

This app was created to allow patrons to easily apply for a library card online and receive immediate access to the libraries online collections and resources. It uses the Sierra API to create a patron record in the ILS with all the required fields and does some rudimentary checks to ensure the person who is filling out the application is a real patron from your municipality.

It does the following:

- Uses Google reCaptcha to ensure the person filling out the application is not a bot.
- Verifies that the postal code matches the address that was provided

- If configured it verifies that the patron&#39;s address comes from the area you define as your catchment area
- That the address provided is not a business address.

The code has been tested using an XAMPP stack on Windows as well as a LAMP stack on Ubuntu.

The code requires a few php extensions to work.

php-gd is used to create the image of the library card and superimpose the barcode on top of it.

Previous versions of the code queried the ILS DB to get a list of patron types to assign to the patron. This was removed for this app as we want all patrons to be of the same patron type. The DB may be used in future versions of the code depending on what iii allows in the SierraView SQL table. To ensure compatibility, in the php.ini file find and uncomment the following extensions:

- pdo\_pgsql
- pgsql

The code uses PHPMailer to send emails to patrons. It has an easier interface to work with inside the code compared to the default PHP mail functions. Follow this link to install composer, and then PHPMailer.

[https://alexwebdevelop.com/phpmailer-tutorial/](https://alexwebdevelop.com/phpmailer-tutorial/)

**CONFIGURATION**

All of the configuration for the app is done inside the config.php file. You will need to get an API key from your Sierra Admin web interface. III provides instructions on how to request an API key in their supportal ([https://iii.rightanswers.com/portal/app/portlets/results/viewsolution.jsp?solutionid=170526042736147&amp;page=1&amp;position=1&amp;q=sierra%20api](https://iii.rightanswers.com/portal/app/portlets/results/viewsolution.jsp?solutionid=170526042736147&amp;page=1&amp;position=1&amp;q=sierra%20api)). After you request the API key you are emailed a link to create the API secret. You need both the key and the secret to make API calls. Enter both of these into the config file. The config file is well commented.

**STARTING BARCODE**

You need to providing a starting number of the barcode range you want to use. Since these are e-patrons and some of them may never set foot in the library you might want to dedicate a range of barcodes that are far away from your physical cards. For instance at my system we use the range 21387000000000 Where the first 6 are always the same and the last numbers differ. We chose to use the range 21387009000000 as the starting barcode. It will be a VERY long time before our system even comes close to that number range. If you end up using something that is in the same range of physical cards then you need to ensure those cards are not actually given to any patrons because duplicate barcodes could be issued.

**LIBRARY CARD IMAGE**

At the end of the application process, the patron is presented with a library card and PIN. The library card shown to the patron is an image that is created on the fly by the code and is customizable to look like library cards from your organization. The image file for the background of the library card is located in the /public/html/images folder and is named card.jpg. The barcode is placed on the card closer to the bottom. An example card (librarycard.jpg) is also located in the folder. The placement of the barcode on the image is dictated by variables in the function &#39;createLibraryCardImage&#39;. Future variations of the code will provide configuration settings to control this. In the interim, if you want to move the barcode you will need to play with the variables.

**ADDRESS VERIFICATION (optional but strongly suggested)**

The code is able to do many types of address verification. One makes sure the postal code matches the address given, and the other makes sure that the patron who is applying is within your catchment area. You will need the following to use address verification:

A Bing Maps API key.

[https://docs.microsoft.com/en-us/bingmaps/getting-started/bing-maps-dev-center-help/getting-a-bing-maps-key](https://docs.microsoft.com/en-us/bingmaps/getting-started/bing-maps-dev-center-help/getting-a-bing-maps-key)

After you get your key fill out this form to get your API key upgraded to a non-profit key.

[https://www.microsoft.com/en-us/maps/contact-us](https://www.microsoft.com/en-us/maps/contact-us)

You will also need a shapefile describing the boudaries of your city. You can usually get a KML file from your GIS department and parse that. More details on the format are in the config file.

**GOOGLE RECAPTCHA (optional but strongly suggested)**

It&#39;s important to ensure that bot&#39;s aren&#39;t filling out your form and creating dummy patrons. The code uses google recaptcha to protect against this. Get a recaptcha key from [https://www.google.com/recaptcha/admin/](https://www.google.com/recaptcha/admin/) and then put your recaptcha info into the config file.

**GOOGLE ANALYTICS (optional but strongly suggested)**

You can get a good sense for how many people are hitting your online card registration page by using Google Analytics (GA). Sign up for a GA account and create a GA property for your Online Card Registration App. Enter the code you receive from the GA setup into the config file. The rest of the code that is placed in the header of all the pages is already included in the code.

Credit needs to be given to all the code I scoured off the internet to make this project possible:

Barcode image generation from Shay Anderson: [http://www.shayanderson.com/php/php-barcode-generator-class-code-39.htm](http://www.shayanderson.com/php/php-barcode-generator-class-code-39.htm)

PHP Mailer to send emails: [https://github.com/PHPMailer](https://github.com/PHPMailer)

Michael&#39;s code to determine if a point is inside a polygon: [https://assemblysys.com/php-point-in-polygon-algorithm/](https://assemblysys.com/php-point-in-polygon-algorithm/)

### Planned updates are:
- Moving the body of the email sent to patrons to an external php file for easier updates
- Adding code to allow for parents to apply for a childs card by authenticating their card during the process
