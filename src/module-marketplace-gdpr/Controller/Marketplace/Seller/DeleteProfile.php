<?php
namespace TCGCollective\MarketplaceGdpr\Controller\Marketplace\Seller;

use Exception;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Lof\Gdpr\Helper\Data;
use Psr\Log\LoggerInterface;
use Lof\MarketPlace\Helper\Data as HelperSeller;
use Lof\MarketPlace\Model\Seller as SellerProfile;

/**
 * Class DeleteProfile
 *
 * @package TCGCollective\MarketplaceGdpr\Controller\Marketplace\Seller
 */
class DeleteProfile extends \Lofmp\GdprSeller\Controller\Marketplace\Seller\DeleteProfile
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var HelperSeller
     */
    private $helperSeller;

    /**
     * @var SellerProfile
     */
    private $sellerProfile;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param HelperSeller $helperSeller
     * @param SellerProfile $sellerProfile
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $registry,
        LoggerInterface $logger,
        HelperSeller $helperSeller,
        SellerProfile $sellerProfile,
        Data $helper
    ) {
        $this->_customerSession    = $customerSession;
        $this->registry            = $registry;
        $this->logger              = $logger;
        $this->helperSeller        = $helperSeller;
        $this->_helper             = $helper;
        $this->sellerProfile       = $sellerProfile;
        parent::__construct(
            $context,
            $customerSession,
            $registry,
            $logger,
            $helperSeller,
            $sellerProfile,
            $helper
        );
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_helper->allowDeleteSeller() || !$this->_customerSession->isLoggedIn() || !$this->canDeleteSeller()) {
            $this->messageManager->addErrorMessage(__('Permission denied.'));
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        try {
            $sellerId = $this->helperSeller->getSellerId();
            if ($sellerId) {
                $seller = $this->sellerProfile->load($sellerId);
                $seller->setShopTitle('')
                    ->setImage('')
                    ->setThumbnail('')
                    ->setAddress('')
                    ->setPaymentSource('')
                    ->setTwitterId('')
                    ->setFacebookId('')
                    ->setGplusId('')
                    ->setYoutubeId('')
                    ->setVimeoId('')
                    ->setInstagramId('')
                    ->setPinterestId('')
                    ->setLinkedinId('')
                    ->setOthersInfo('')
                    ->setBannerPic('')
                    ->setCompanyLocality('')
                    ->setCountryPic('')
                    ->setCountry('')
                    ->setCompanyDescription('')
                    ->setContactNumber('')
                    ->setCountryId('')
                    ->setCompany('')
                    ->setCity('')
                    ->setRegion('')
                    ->setStreet('')
                    ->setRegionId('')
                    ->setPostcode('')
                    ->setTelephone('')
                    ->setShippingPolicy('')
                    ->setReturnPolicy('');
                $seller->setData('name','');
                $seller->setData('address_line_1','');
                $seller->setData('address_line_2','');
                $seller->setData('description','');
                $seller->setData('website_url','');
                $seller->setData('ship_to','');
                $seller->setData('ship_to_country','');
                $seller->setData('term_and_conditions','');
                $seller->setData('shipping_policy','');
                $seller->setData('return_policy','');
                $seller->setData('operating_hours','');
                $seller->save();
                $this->messageManager->addSuccessMessage(__('You have deleted your seller profile.'));
            }
            $resultRedirect->setPath('*/*/');
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something wrong while deleting your seller profile. Please contact the store owner.'));
            $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }

    /**
     * @return array|mixed
     */
    public function canDeleteSeller() {
        return $this->_helper->getConfigGeneral('allow_delete_seller');
    }
}
