Magento Patch finder (Magepatch)
================================

| Branch        | Status       | Version  |
| ------------- |:-------------|:---------|
| Master        | [![Build Status](https://travis-ci.org/frosit/magepatch.svg?branch=master)](https://travis-ci.org/frosit/magepatch) | ---------|
| Staging       | [![Build Status](https://travis-ci.org/frosit/magepatch.svg?branch=staging)](https://travis-ci.org/frosit/magepatch)  | ---------|

The following CLI tool can aid with patch management of your Magento 1 store. The console utility/phar file basically finds the right patches for your Magento installation based on the stores' version and edition. It contains an index of all patches and uses [magento-patches repository](https://github.com/brentwpeterson/magento-patches) as it's data source. This data is aggregated and reviewed before added to the index.
If you spot an issue, you can modify the json file in the res directory.

__Note: work in progress, feel free to contribute__

__Install__

```bash
wget -q -O magepatch.phar http://magepatch.gdprproof.com/magepatch.phar && chmod +x magepatch.phar
```

__Build (dev)__

```bash
git clone https://github.com/frosit/magepatch.git && \
cd magento-patch-finder && make install && make build
```


__Commands__

* _patches:find_
    * finds patches based on your version number
    * compares towards applied.patches.list
    * allows to download missing patches
* _patches:show-applied_
    * Show what is applied and what not
* _patches:extract-diff_
    * Extracts the .diff / .patch parts from the patch
    
__Troubleshooting__

It should work if you drop it into a bin folder and navigate to a mage root folder. Additionally you could keep the .phar in your mage root as well.
Else you could use these options.

* --mage=\[directory\] | set a fixed path to the mage root dir.
* --nomage | don't load mage, in case you want to quickly check the amount of indexed patches or something

    
__To Do__

* Review and clean code
* Remove deprecated code
* Create tests with CI
    * Fix broken tests
    * extend tests to other classes
    * extend spec
    * add codecov
* improve exception handling
* Show phar version
* phar selfupdate
* Fix travis Magento install script
* Add patch installers to tests, or applied.patches fixtures
* Extend documentation
* Convert bullets to issues
* Magerun integration?

[![codecov](https://codecov.io/gh/frosit/magepatch/branch/master/graph/badge.svg)](https://codecov.io/gh/frosit/magepatch)

__Author__

Fabio Ros - FROSIT (@frosit_it)
