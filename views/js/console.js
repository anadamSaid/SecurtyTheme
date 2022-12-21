/**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.setInterval(function(){console.clear()},100),Object.defineProperty(console,"_commandLineAPI",{get:function(){throw"Console is disabled"}});

devtoolsDetector.addListener(function(isOpen) {
    if(isOpen){
        document.body.innerHTML = "<h1>DevTools Is Detected !! This Webseit don't Allow users to See Content</h1>" ;
    }
    });
    
devtoolsDetector.launch();

