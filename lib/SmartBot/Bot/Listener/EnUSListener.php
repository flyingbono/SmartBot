<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/flyingbono/SmartBot>.
 */
namespace SmartBot\Bot\Listener;

use SmartBot\Bot\ListenerAbstract;
use SmartBot\Bot\ListenerInterface;

/**
 * SmartBot EnUSListener Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class EnUSListener extends ListenerAbstract implements ListenerInterface
{
    
    /**
     *
     * {@inheritDoc}
     * @see \SmartBot\Bot\ListenerInterface::initialize()
     */
    public function initialize()
    {
        
        $bot = $this -> smartBot;
                
        // Create listeners and associate to responders
        $bot -> listen(['/__test__/i'], $this->responder('__test__'));
        $bot -> listen(['/(hello| hi |good morning)/i'], $this->responder('hello'));
        $bot -> listen(['/what time is it/i'], $this->responder('time'));
        $bot -> listen(['/my ([a-z]+) is ([a-z]+)/i'], [$this->responder('acquire'),$this->responder('acquired')]);
        $bot -> listen(['/(what).+(your|ur).+(name).+\?/i'], $this->responder('whoami'));
        $bot -> listen(['/(who).+(are|r|is).+(you|u).+\?/i'], $this->responder('whoami'));
        $bot -> listen(
            ['/(wher).+(are|r).+(you|u).+\?/i', '/(wher).+(you|u).+(leave|come from).+\?/i'],
            $this->responder('whereami')
        );
        
        $bot -> listen(
            ['/who is ([a-z ]+) ?/i','/what is ([a-z ]+)/i'],
            function ($message, $args) {
                    return $this -> whois($message, $args);
            }
        );
        
        return $this;
    }
    
    public function whois($message, $args)
    {
        $recipient  = $this -> smartBot -> findEntity($args[0]);
        
        if ($recipient instanceof Item) {
            // recipent found. maybe confirm ??
            return $this -> responder('whois-friend') -> handle($message, $args);
            
        } elseif (is_array($recipient) && count($recipient) > 1) {
            return $this -> responder('whois-many') -> handle($message, $args);
            
        } elseif (is_array($recipient) && count($recipient) == 0) {
            // find the first one on internet ??
            $url = 'https://en.wikipedia.org/w/api.php?format=json&action=query&'.
                    'prop=extracts&exintro=&explaintext=&redirects&titles='.urlencode($args[0]);
            $data = file_get_contents($url);
            $response = json_decode($data);
        
            $what = $args[0];
            if (true == isset($response -> query -> redirects)) {
                $what = urlencode(str_replace(' ', '_', $response -> query -> redirects[0] -> to));
            }
            
            foreach (get_object_vars($response -> query -> pages) as $page) {
                if (false == isset($page -> extract)) {
                    return $this -> responder('noresponse') -> handle($message);
                }
                    
                $phrases    = explode('.', $page -> extract);
                $lines      = explode(PHP_EOL, $phrases[0]);
                 
                if (count($lines) > 3) {
                    $lines = array_slice($lines, 0, 3);
                }
                
                $params = array(join(' ', $lines), 'https://en.wikipedia.org/wiki/'.$what );
                return $this -> responder('whois-wiki') -> handle($message, $params);
            }
        
        }
         
        return [];
    }
}
