# Aoe_AsyncCache

## Overview

Aoe_AsyncCache extends the Magento cache management in a way that cache-purges are collected and queued instead of directly purged.

A cronjob runs automatically and purges the cache in a given interval, e.g. once per hour.

This extension speeds up Magento in situations where the cache gets flushed quite often.

### Important

Blocking standard cache-flushed means that there might be old cached data still used instead of up to date data. Please keep this in mind when developing and running in production and adjust the cronjob for your needs.

### Use with Magento EE FPC

In order to use with Magento 1,x EE's FPC, a small configuration change is required.  Without this change, FPC page and container cache entries simply won't be purged.

In your full_page_cache configuration, add this next to `<backend_options>`:

    <frontend_options>
        <!-- Stores clean operations on the queue as FPC entries. -->
        <async_cache_type>full_page_cache</async_cache_type>
        <!-- Set to 1 to allow direct frontend purges, such as the cart. -->
        <async_cache_admin_only>1</async_cache_admin_only>
    </frontend_options>

If `async_cache_admin_only` is set to 0 or not present, partially cached pages may not get updated with recent cart changes and similar.
