<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\ContactUsInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
 
class ContactUs implements ContactUsInterface
{
	protected $request;
	
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		Context $context,
		\Magento\Framework\App\Request\Http $request,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null,
		array $data = []
	) {
		$this->request						=	$request;
		$this->context = $context;
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
	}
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function ContactUs() {
		
		$user_data 		=	json_decode($this->request->getContent(),true);
		//$request = $this->request();
        if (trim($user_data['name']) === '') {
            throw new LocalizedException(__('Name is missing'));
        }
		if (trim($user_data['phone']) === '') {
            throw new LocalizedException(__('Phone is missing'));
        }
        if (trim($user_data['comment']) === '') {
            throw new LocalizedException(__('Comment is missing'));
        }
        if (false === \strpos($user_data['email'], '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }
        
		
		$this->mail->send($user_data['email'],['data' => new DataObject($user_data)]);
		
		return true; 
    }
}