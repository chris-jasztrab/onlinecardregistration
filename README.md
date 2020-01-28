# Sierra Online Card Registration

## Presented at OLA 2020

#### Background

This repository was created by Chris Jasztrab and Kanta Kapoor and is the initial code release for the Online Patron Registration module that was created by MPL.  Knowledge of the Sierra API is required and you will need an API key for your ILS to get started.

It is strongly suggested that whomever is implementing this code has a background in PHP as the entire codebase is PHP and although I've done my best to provide comments in the code, there will be some modifications you're going to want to make.  Someone with a good understanding of PHP should be able to take the functions libraries and create their own form to gather the data and post it. 

MPL chose to create our own online card registration system so that we could customize the look and feel of the site and add additional branding to the email that is sent to the patron.  Also this doesn't cost us anything other than the time it took to code it.  Bing Maps is one of the external vendors that this system relies on and they provide API access for free to non-profits.  You will need to apply for an API key with Bing in order for this to work.
If you choose to not do all the address checks and don't have a non-resident fee, than you can eliminate a lot of the address checks.  

### Planned updates are:
- Switches in the config file to disable the various address checks
- Moving the body of the email sent to patrons to an external php file for easier updates
- Adding code to allow for parents to apply for a childs card by authenticating their card during the process


