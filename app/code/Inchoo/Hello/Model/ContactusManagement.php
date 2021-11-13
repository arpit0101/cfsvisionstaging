<?php
declare(strict_types=1);

namespace Inchoo\Hello\Model;

use Magento\Contact\Model\MailInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;
use Inchoo\Hello\Api\ContactusManagementInterface;

/**
 * Class ContactusManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContactusManagement implements ContactusManagementInterface
{ 
    private $mail;

    protected $dataObjectFactory;

    public function __construct(
        MailInterface $mail,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->mail = $mail;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
    * @inheritDoc
    */
    public function submitForm($contactForm) {
		$result = $this->dataObjectFactory->create();

        if (empty($contactForm['name'])) {
            $result->setData('message', 'Enter the Name and try again.');
            return $result;
        }
        if (empty($contactForm['email'])) {
            $result->setData('message', 'Enter the Email and try again.');
            return $result;
        }
        if (false === \strpos($contactForm['email'], '@') || false === \strpos($contactForm['email'], '.com')) {
            $result->setData('message', 'The email address is invalid. Verify the email address and try again.');
            return $result;
        }
        if (empty($contactForm['comment'])) {
            $result->setData('message', 'Enter the Comment and try again.');
            return $result;
        }

        try {
            $this->sendEmail($contactForm);
            $result->setData('message', 'Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.');
        } catch (LocalizedException $e) {
            $result->setData('message', $e->getMessage());
        } catch (\Exception $e) {
            $result->setData('message', 'An error occurred while processing your form. Please try again later.');
        }
        return $result;
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }
}