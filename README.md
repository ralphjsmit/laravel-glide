![laravel-glide](https://github.com/ralphjsmit/laravel-glide/blob/main/docs/images/laravel-glide.jpg)

# Never worry about manually rescaling static images again!

Currently, it's almost a requirement to **load images** on websites in such a way that they are **responsive** and not unnecessarily big. This means that every image should be **scaled down to a variety of sizes**, so that browser on smaller screens don't have to download a large image unnecessarily. This technique is accomplished by using `srcset` and `sizes` attributes on each `img` tag.

However, if you  **receive a 3.000 x 2.000px** image from your client, you **don't want** to put this into Figma or other tool, **generate 5 versions**, name them in a sensible way, manually put them in the correct public folder, etc. This is just a tremendous hassle, whereas usually you just want to **drop in the original image** in your project, refer to it via a `src` and be done with it. This package aims to solve this problem in a simple and sensible way.

Instead of **manually needing to generate all these images**, we can use an image generator like [Glide](https://glide.thephpleague.com/). This package provides a simple way to reuse or scale images. 

## Installation

Run the following command to install the package:

```bash
composer require ralphjsmit/laravel-glide
```

You do not need to publish a config file or anything else.

## Usage

In order to demonstrate this package's usage, we'll use the following example. Previously, you **would include** an image like this:

```blade
<img src="{{ asset('img/my-huge-image.png') }}" alt="Some alt text" />
```

This loads the `my-huge-image.png` on it's full resolution on every screen size. **With this package**, you'd do this:

```blade
<img {{ glide()->src('img/my-huge-image.png') }} alt="Some alt text" />
```

Under the hood, this will be **converted to the following** output:

```blade
<img 
    src="https://your-app.com/img/my-huge-image.png" 
    srcset="
        https://your-app.com/glide/img/my-huge-image.png?width=400 400w, 
        https://your-app.com/glide/img/my-huge-image.png?width=800 800w, 
        https://your-app.com/glide/img/my-huge-image.png?width=1200 1200w, 
        https://your-app.com/glide/img/my-huge-image.png?width=1600 1600w, 
        https://your-app.com/glide/img/my-huge-image.png?width=2000 2000w, 
        https://your-app.com/glide/img/my-huge-image.png?width=2500 2500w, 
        ...
    " 
    loading="lazy"
    alt="Some alt text" 
/>
```
                                                                         
If your **browser** receives the above code, it will **determine the optimal image size**. Say that your browser is 800px wide at a resolution of `2x`, then it would be optimal to have an image of 1600px. Your browser will then look into the `srcset` and it will take the URL for the 1600px version (https://your-app.com/glide/img/my-huge-image.png?width=1600). The browser will then call this URL and **Glide will generate the 1600px image version** for the browser and return it.
                   
Glide will **cache all images**, so that it doesn't have to generate the same image over and over again. Even on the first image, Glide will still be vary fast always.

Because the browser in this case only requests the 1600px, the other URLs are **not called** and therefore also **not processed** by Glide. This solution is therefore perfect, because it will only do the **minimum amount of work**.

Your image will automatically be upscaled to a maximum of 2x the resolution provided in your original image.

The `glide()->src()` function is even **auto-completed** if you use Laravel Idea to the files in your public-/asset-path.

### Setting a maximum width

Say that your image is 2000px wide. However, you have displayed in such a way that it will **at its biggest only be a 1000px wide**. In that case, pass a second parameter to the `glide()->src()` function to set a maximum width:

```blade
<img {{ glide()->src('img/my-huge-image.png', 1000) }} alt="Some alt text" />
```

This will output the image variations up to the last version that fits inside the maximum width, plus an image at exactly the maximum width, but not wider. Also, your original `src` will also inherit this maximum width:

```blade
<img 
    src="https://your-app.com/glide/img/my-huge-image.png?width=1000" 
    srcset="
        https://your-app.com/glide/img/my-huge-image.png?width=400 400w, 
        https://your-app.com/glide/img/my-huge-image.png?width=800 800w, 
        https://your-app.com/glide/img/my-huge-image.png?width=1000 1000w, 
    " 
    loading="lazy"
    alt="Some alt text" 
/>
```

### Specifying a `sizes` attribute as well

You can provide a `sizes` attribute as well. This attribute is handy to tell the browser what **width an image will approximately have at a certain breakpoint**. You can give any value you want. This is an example of showing that on screens smaller than 500px, the image is approximately full-width, and on screens above (the default), it is approximately 50% of the screen width: 

```blade
<img {{ glide()->src('img/my-huge-image.png', 1000, sizes: '(max-width: 500px) 100vw, 50vw') }} alt="Some alt text" />
```

Which will result in:

```blade
<img 
    src="https://your-app.com/glide/img/my-huge-image.png?width=1000" 
    srcset="
        https://your-app.com/glide/img/my-huge-image.png?width=400 400w, 
        https://your-app.com/glide/img/my-huge-image.png?width=800 800w, 
        https://your-app.com/glide/img/my-huge-image.png?width=1000 1000w, 
    " 
    sizes="(max-width: 500px) 100vw, 50vw"
    loading="lazy"
    alt="Some alt text" 
/>
```

### Eager loading images

HTML allows for a native way to lazy load images if they are below the fold. This reduces the initial page size and speeds up the page load. Lazy loading for images is enabled by default for URLs outputted by Glide.

However, if you need to eager load an image, you can pass `lazy: false` to disable lazy loading for an image. You should only do this for images that are above the fold on initial page load, otherwise it will slow down your page unnecessarily.

```blade
<img {{ glide()->src('img/my-huge-image.png', 1000, sizes: '(max-width: 500px) 100vw, 50vw', lazy: false) }} alt="Some alt text" />
```

Which will result in:

```blade
<img 
    src="https://your-app.com/glide/img/my-huge-image.png?width=1000" 
    srcset="
        https://your-app.com/glide/img/my-huge-image.png?width=400 400w, 
        https://your-app.com/glide/img/my-huge-image.png?width=800 800w, 
        https://your-app.com/glide/img/my-huge-image.png?width=1000 1000w, 
    " 
    sizes="(max-width: 500px) 100vw, 50vw"
    <!-- Loading attribute is omitted, so it will be eager loaded -->
    alt="Some alt text" 
/>

```

### Clearing cache

If you want to **clear the cache**, you can call the following command:

```bash
php artisan glide:clear
```

This will **empty the entire Glide cache**. You can choose to put this in your deployment script on production if you often _modify_ your current images (adding new images has no effect on the cache, they will just be generated anew). Another option is to only run this command when you actually _modify_ an existing image and then run this command manually via SSH on the server in that situation.

## Glide Configuration

Currently, the package **does not provide configuration** options and it just assumes **sensible defaults**.

Since it is geared at auto-generating versions for static images, it will **assume** the `public_path()`/`asset()` as root folder for the images.

The cache is positioned at the `storage/framework/cache/glide` folder.

Currently, it is not possible to **modify these locations**. However, that would not be so hard to implement. If you have a use case for this, please let me know via the issues or provide a PR.

## Roadmap

I hope this package will be useful to you! If you have any ideas or suggestions on how to make it more useful, please let me know (rjs@ralphjsmit.com) or via the issues.

PRs are welcome, so feel free to fork and submit a pull request. I'll be happy to review your changes, think along and add them to the package.

## Credits

This package was partially inspired by the archived [flowframe/laravel-glide](https://github.com/Flowframe/laravel-glide) by [Lars Klopstra](https://github.com/larsklopstra).

## General

🐞 If you spot a bug, please submit a detailed issue and I'll try to fix it as soon as possible.

🔐 If you discover a vulnerability, please e-mail rjs@ralphjsmit.com.

🙌 If you want to contribute, please submit a pull request. All PRs will be fully credited. If you're unsure whether I'd accept your idea, feel free to contact me!

🙋‍♂️ [Ralph J. Smit](https://ralphjsmit.com)
