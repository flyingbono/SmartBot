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
namespace SmartBot\Bot\Responder;

/**
 * SmartBot Brain Responder Response Class.
 *
 * @author Bruno VIBERT <bruno.vibert@bonobox.fr>
 */
class Response
{
    
    /**
     * The rules to check
     * 
     * @var string
     */
    protected $_rule = null;
    
    /**
     * The response
     * 
     * @var string
     */
    protected $_message;
    
    /**
     * Class constructor
     * 
     * @param string $message
     * @param string $rule
     */
    public function __construct( $message, $rule = null )
    {
        $this -> _rule     = $rule;
        $this -> _message  = $message;
        
    }
    
    /**
     * Get the response rule
     * 
     * @return string
     */
    public function getRule()
    {
        return $this -> _rule;
    }
    
    /**
     * Get the response message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this -> _message;
    }
}
