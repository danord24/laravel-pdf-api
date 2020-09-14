## About Laravel PDF API

Laravel PDF API does as the name says! It is a quick and simple API which allows users to create a PDF via a live URL or from raw HTML. It leverages the excellent [Browershot package by Spatie](https://github.com/spatie/browsershot). Some of it's features are:

- Generate a PDF from a live URL or raw HTML
- Save details and path to the PDF in a database
- Upload documents to a cloud provider (S3, Digital Ocean Spaces)
- Stream the raw PDF back to the client, great for outputting the PDF in the browser or for passing it onto an email as an attachment.

## Requirements

You'll need to meet the requirements for v8 of Laravel and to also have the latest version of Node JS installed. This project makes use of the Spatie Browsershot package which uses Puppeteer. 

## Installation

Clone the repository as normal. You will need to setup some .env variables in order to make the PDF generation work correctly.

Varibles needed are as follows:

```
AWS_ACCESS_KEY_ID=YOURKEY 
AWS_SECRET_ACCESS_KEY=YOUR_SECRET
AWS_DEFAULT_REGION=eu-west-2
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket-name.s3.your-region.amazonaws.com

NODE_PATH="YOUR PATH TO NODE"
NPM_PATH="YOUR PATH TO NPM"
```

If you are using Digital Ocean Spaces for your cloud storage, you can follow this guide on [how to setup Digital Ocean Spaces with Laravel.](https://www.danielord.co.uk/upload-and-fetch-images-using-digital-ocean-spaces-and-laravel-5)

You can find out where your node and NPM lives by typing in `which node` and `which npm` into your terminal.

After you've added your variables, you should do an `npm install` to download Puppeteer and any other dependancies.

If you are running this locally, you can use `php artisan serve` to spin up a webserver and test the API out via Postman.

If you plan to deploy this to production, you can follow the steps for [deploying with Laravel Forge](https://github.com/spatie/browsershot#requirements) on the Browsershot documentation. Using Forge isn't a requirement, but the instructions are helpful for Ubuntu installs nonetheless. 

## Usage

You can import the [Postman collection](https://github.com/danord24/laravel-pdf-api/blob/master/postman_collection.json) which will give you example endpoints for Creating, Reading, Updating and Deleting Documents (PDF's).

### Creating Documents (PDF's)
As well as generating PDF's from URLs and HTML, this project will also allow you to store details about the document in a database. 

Information you can store agaisnt a document is as follows:

- name
- description
- file_path - path on cloud storage
- orientation - landscape or portrait
- visibility - public or private with a time sensitive URL
- json - any JSON you want to store to do text replacement for dynamically generated documents. *Coming Soon*

The visibility field allows you to specify whether you want the document to be publically visibile on the Internet or private, which can only be accessed via a signed url.

If you want to make a document private, you can also specifiy a duration field which makes the signed URL exipre after a time period you specifiy in minutes.

### Streaming the PDF

If needed, you can stream the PDF back to the client. This can be useful for returning the PDF back to the browser for immediate viewing or you can then take the stream and pass it to another function, such as an attachment in an email.

To stream the PDF, simply pass in `stream_file: true` and the PDF will be streamed back to you with a content type of 'application/pdf'.

## Fetching Documents

Two endpoints exist to fetch your documents. You can fetch all your documents or fetch a document by its ID.

Fetching documents by their IDs also returns the public facing or signed URL. Fetching all documents will only return the path to the document.

## Updating Documents

You can update the following details of your document:

- name
- description
- orientation
- visibility

## Deleting Documents

Deleting a document will permanently delete the file from the cloud storage and from the database.

## Authentication

Out of the box this API comes with no Authentication enabled. You are free to use Laravel Passport, Sanctum or a method of your chosing to help restrict access to your API endpoints.