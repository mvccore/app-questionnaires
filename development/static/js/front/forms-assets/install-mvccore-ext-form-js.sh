# create new mpty directory "mvccore-ext-form-js"
mkdir mvccore-ext-form-js
# clone this repository into newly created folder
git clone https://github.com/mvccore/ext-form-js mvccore-ext-form-js
# go to repository folder
cd mvccore-ext-form-js
# go to repository latest release (optional)
php -r "$a=shell_exec('git ls-remote --tags .');$b=explode('refs/tags/',$a);$c=trim($b[count($b)-1]);shell_exec('git checkout tags/'.$c);"
# remove whole '.git' directory, git history (you don't need this repository history in your project repo)
rm -r -f .git
# load this node package dependencies
sh -c "npm update"
# call this node package install script
sh -c "node install.js"
# go to start parent directory
cd ../..