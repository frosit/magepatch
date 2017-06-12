Magento Patch finder
===================
    
The following is a small phar solution i made based on the patch verification solution within my Magento Security Framework. The console utility basically finds the right patches for your Magento installation based on the stores' version and edition. I've aggregated an index of all patches for this as part of my security solution. This indexed data is being matched against the store using semantic versioning, and filtered using the applied.patches file. It provides some additionally features to aid in patch work like auto-downloading, extracting the diff's, matching checksums etc.

__Note: work in progress__

[Magento Patch Finder](https://github.com/frosit/magepatch)

All patches and information used are publicly available in the [magento-patches](https://github.com/brentwpeterson/magento-patches) repository which is now according to my data, complete. My framework has  features for collecting and aggregating this kind of patch data which i then contribute. The features that aggregate the data are not in there. My framework periodically updates the [patch index json file](http://magepatch.gdprproof.com/patches.json).

__Install__

```bash
wget -q -O magepatch.phar http://magepatch.gdprproof.com/magepatch.phar && chmod +x magepatch.phar
```


__Build (dev)__

```bash
git clone https://github.com/frosit/magepatch.git && \
cd magento-patch-finder && make install && make build
```

__ASCIICast__

[![asciicast](https://asciinema.org/a/e5vm43gygt1m2wx9d9q4ccxbb.png)](https://asciinema.org/a/e5vm43gygt1m2wx9d9q4ccxbb)


__Actions__

* patches:find
    * finds patches based on your version number
    * compares towards applied.patches.list
    * allows to download missing patches
* patches:show-applied
    * Show what is applied and what not
* patches:extract-diff
    * Extracts the .diff / .patch parts from the patch


__About__

When working with large stores, having to verify dozens of patches in complex and highly customised installations can be quite the task. In order to ensure a store's security, I had to make sure everything that was necessary to patch, was patched. When i found out the sources i relied on we're not complete, i started maintaining my own collection. Now the collection is complete, I've extracted some features out of my framework into this Symfony CLI tool for the community to use.

My security framework has ben under development for over a year now and is coming close to a first release. This framework aids us in providing semi-automated security & monitoring services as well as judicial and compliancy related consultancy. Feel free to drop me a pm if you're interested to have a chat about that.

__Author__

Fabio Ros - GDPRProof.com (@frosit_it)    