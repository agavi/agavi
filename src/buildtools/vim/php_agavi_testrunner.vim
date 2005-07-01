" php_agavi_testrunner.vim -- Run unit tests
" @Author:			Mike Vincent (mike@agavi.org)
" @License:			GPL (see http://www.gnu.org/licenses/gpl.txt)
" @Created:			2005-05-01
" @Last Change:	2005-06-24
" @Revision:		0.5
"
"
" Inspired by Thomas Link's php syntax checking script,
" (http://www.vim.org/scripts/script.php?script_id=1272)
" I expanded upon his script so I could utilize the quickfix features while
" developing in a unit tested environment. 
"
" This is a filetype plugin, you can drop in you ~/.vim/ftplugin/ directory
" There is only one configurable, php_testrunner_cmd, set it to the command
" to run the unit tests, eg by adding this to your .vimrc:
" 
"  let g:php_testrunner_cmd = 'agavi test -Dreporter=vim'
"
"
" TODO: figure out how to colorize things nicely. 
" 			It would be nice if the summary line could be done with a GREEN bg 
" 			and white text if we have all passes else RED bg and white text to 
" 			follow the red, green, refactor theme.

if exists("g:php_run_tests")
	finish
endif

let g:php_run_tests = 1

" The command we will issue to run the unit test suite
" you should configure this from your .vimrc
if !exists('g:php_testrunner_cmd')
	let g:php_testrunner_cmd = 'agavi test -Dreporter=vim'
endif

function! PhpRunTests()
    if &filetype == 'php'
				" save these so we can restore them back
        let t  = @t
        let mp = &makeprg
        " let sp = &shellpipe
        let ef = &errorformat
				
        try
            let &makeprg = g:php_testrunner_cmd
						" These are mostly specific to agavi's environment and
						" expect we'll be using the vim reporter. 
						set efm=%EFailure\ #%n)\ Line:\ #%l\ File:\ %f\ Msg:\ %m,
										\%-G\\s%#,
										\%-G\%.%#,
										\%-Gagavi\ >\ test:,
										\%-GBUILD%.%#,
										\%-G%.%#FAILED!,
										\%+GTest\ cases\ run:\ %m,
										\%EParse\ error:\ %m\ in\ %f\ on\ line\ %l,
										\%EFatal\ error:\ %m\ in\ %f:%l,
            silent make %
            redir @t
            silent clist
            redir END
						copen
        finally
            let @t = t
            let &makeprg     = mp
            " let &shellpipe   = sp
            let &errorformat = ef
        endtry
    endif
endf

"	^F6 will run the tests
noremap <buffer> <C-F6> :call PhpRunTests()<cr>
inoremap <buffer> <C-F6> <esc>:call PhpRunTests()<cr> 
" Every time we save a php file, the suite will run
autocmd BufWritePost *.php call PhpRunTests()
