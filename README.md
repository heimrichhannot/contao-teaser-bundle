# Contao Teaser Bundle

Teaser is a contao content element, that provides the ability to link the element with pages, articles, files, downloads and external urls.
Can be used as replacement for ce_page_teaser. 

## Features
 
* Custom text and css-class for more link
* Link the whole article
* Hide more link, link entire element.
* Overwrite content template

![Teaser Frontend](docs/img/contao-teaser-bundle_screenshot_frontend.png)

![Teaser Backend](docs/img/contao-teaser-bundle_screenshot_backend.png)
    
## Extend

### Templates

* Element template prefix: `ce_linkteaser_`.
* Image template prefix: `linkteaser_content_image_`.

### Hooks

Name | Arguments | Expected return value | Description
---- | --------- | --------------------- | -----------
generateTeaserLink | $element: LinkTeaserElement, $showMore: bool | $showMore: bool | Add custom teaser source and modify the content element. 
getContentSourceOptions | $options: array, $dc: DataContainer | $options: array | Add custom source options to the dca.