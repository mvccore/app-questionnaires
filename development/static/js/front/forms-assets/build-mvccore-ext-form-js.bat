@set currentDir=%cd%
@cd mvccore-ext-form-js/dev-tools
@call build.cmd
@cd %currentDir%/../../../../Var/Tmp
@del /F /Q *.js
@del /F /Q *.css
@cd %currentDir%