# Aoe_AsyncCache

## Overview

Aoe_AsyncCache extends the Magento cache management in a way that cache-purges are collected and queued instead of directly purged.

A cronjob runs automatically and purges the cache in a given interval, e.g. once per hour.

This extension speeds up Magento in situations where the cache gets flushed quite often.

### Important

Blocking standard cache-flushed means that there might be old cached data still used instead of up to date data. Please keep this in mind when developing and running in production and adjust the cronjob for your needs.

### Use with Magento EE FPC

Magento 1.x EE's FPC is supported, and purges are queued against it as well.  In some cases, you may wish for user-triggered FPC purges to occur immediately.  It's possible to make queuing apply only to actions from the admin.

In your full_page_cache configuration, add this next to `<backend_options>`:

    <frontend_options>
        <!-- Set to 1 to allow direct frontend purges, such as the cart. -->
        <async_cache_admin_only>1</async_cache_admin_only>
    </frontend_options>
