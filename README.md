# Magento 2 Module
## Usage
### 1) Create di.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <virtualType name="Example\SomeExtension\Model\ProductPageSmallImageConfig"
                 type="IntegrationHelper\BaseImage\Model\ImageConfig">
        <arguments>
            <argument name="imagePath" xsi:type="string">catalog/product</argument>
            <argument name="imageDestinationPath" xsi:type="string">catalog/product/resized</argument>
            <argument name="uniqueTypeId" xsi:type="string">product_page_small_image</argument>
            <argument name="constraintOnly" xsi:type="boolean">true</argument>
            <argument name="keepTransparency" xsi:type="boolean">true</argument>
            <argument name="keepFrame" xsi:type="boolean">true</argument>
            <argument name="keepAspectRatio" xsi:type="boolean">true</argument>
            <argument name="width" xsi:type="string">100</argument>
            <argument name="height" xsi:type="string">100</argument>
            <argument name="quality" xsi:type="string">100</argument>
        </arguments>
    </virtualType>
    
    <!-- Resizer usage >>>>>>>>>>>>>>>   -->

    <virtualType name="Example\SomeExtension\Model\ProductPageSmallImageResizer" 
                 type="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageResizer\ImageResizer">
        <arguments>
            <argument name="loggerType" xsi:type="string">resizer_product_image_crit</argument>
            <argument name="imageConfig" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageConfig</argument>
        </arguments>
    </virtualType>

    !!!! Important to use ImageResizerPool, cause it can be use for clear resized folder from old images
    <virtualType 
        name="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageResizerPool"
        >
        <arguments>
            <argument name="processes" xsi:type="array">
                <item name="product_page_small_image" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageResizer</item>
            </argument>
        </arguments>
    </virtualType>
    <!-- <<<<<<<<< Resizer usage  -->
    
    
    <!-- SAVER >>>>>>>   -->
    <virtualType name="Example\SomeExtension\Model\ProductPageSmallImageSaver"
                 type="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageSaver\ImageSaver">
        <arguments>
            <argument name="loggerType" xsi:type="string">saver_product_image_crit</argument>
            <argument name="imageConfig" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageConfig</argument>
        </arguments>
    </virtualType>
    <virtualType name="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageSaverPool"
                 >
        <arguments>
            <argument name="processes" xsi:type="array">
                <item name="product_page_small_image" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageSaver</item>
            </argument>
        </arguments>
    </virtualType>
    <!-- <<<<<<< SAVER   -->
    
    <!-- UPLOADER >>>>>>>   -->
    <virtualType name="Example\SomeExtension\Model\ProductImageUploader"
                 type="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageUploader\ImageUploader">
        <arguments>
            <argument name="loggerType" xsi:type="string">uploader_product_image_crit</argument>
            <argument name="imageConfig" xsi:type="object">IntegrationHelper\BaseImage\Model\ProductImageUploaderConfig</argument>
            <argument name="sources" xsi:type="array">
                <item name="source_example" xsi:type="object">Example\SomeExtension\Model\UploaderImageSource</item>
                <!--                Example\SomeExtension\Model\UploaderImageSource must implement IntegrationHelper\BaseImage\Model\ImageProcessors\ImageUploader\SourceInterface-->
            </argument>
        </arguments>
    </virtualType>
    
    <virtualType name="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageUploaderPool"
                 >
        <arguments>
            <argument name="processes" xsi:type="array">
                <item name="product_image" xsi:type="object">Example\SomeExtension\Model\ProductImageUploader</item>
            </argument>
        </arguments>
    </virtualType>
    <!-- <<<<<<< UPLOADER   -->
    
    <!-- Cleaner usage >>>>>>>>> -->
    <virtualType name="Example\SomeExtension\Model\ProductPageSmallImageCleaner"
                 type="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageCleaner\ImageCleaner">
        <arguments>
            <argument name="loggerType" xsi:type="string">cleaner_product_image_crit</argument>
            <argument name="imageConfig" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageConfig</argument>
        </arguments>
    </virtualType>

    !!!! Important to use ImageResizerPool, cause it can be use for clear resized folder from old images
    <virtualType name="IntegrationHelper\BaseImage\Model\ImageProcessors\ImageCleanerPool"
                 >
        <arguments>
            <argument name="processes" xsi:type="array">
                <item name="product_page_small_image" xsi:type="object">Example\SomeExtension\Model\ProductPageSmallImageCleaner</item>
            </argument>
        </arguments>
    </virtualType>
    <!-- <<<<<<<<< Cleaner usage  -->
</config>
```

### 2) Create And Get Resize Url

```php
namespace Example\SomeExtension\Block;

use IntegrationHelper\BaseImage\Api\ImageProcessorManagerInterface;
use IntegrationHelper\BaseImage\Api\ImageProcessorArgInterface;
use Example\SomeExtension\Model\Entity;

class SomeTemplate {
    public function __construct(
        ...
        protected ImageProcessorArgInterface $imageProcessorArg,
        protected ImageProcessorManagerInterface $imageProcessorManager
    ){}
    
    public function resizeAndGetUrl(Entity $entity)
    {
        try {
            $arg = $this->imageProcessorArg->setArgs([
                'name' => 'product_page_small_image',
                'image' => $entity->getImageName()
            ]);
            $processName = 'resizer'; //'saver', 'cleaner', 'optimizer'
            $result = $this->imageProcessorManager->runProcessByNameAndGetResult($processName, $arg);
            $image = $result->getData(); 
        } catch (\Throwable $e) {
            $image = '';
        }
        
    
        return $image;        
    }
}

```

### 3) Get Resized Image Url inside template

```phtml
/**
* @var \Example\SomeExtension\Block\SomeTemplate $block
*/
    $imageResizedUrl = $block->resizeAndGetUrl($block->getEntity());

```
