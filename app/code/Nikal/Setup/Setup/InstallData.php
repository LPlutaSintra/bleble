<?php
namespace Nikal\Setup\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use \Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\Data\Design\Config as DesignConfig;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD)
 */
class InstallData implements InstallDataInterface
{
    const THEME_FULL_PATH = 'frontend/Nikal/esashop';

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var ReinitableConfigInterface
     */
    protected $reinitableConfig;

    /**
     * @var \Magento\Theme\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\View\Design\Theme\ListInterface $themeList
     */
    private $themeList;

    /**
     * InstallData constructor.
     *
     * @param \Magento\Framework\View\Design\Theme\ListInterface $themeList
     * @param \Magento\Theme\Model\Config $config
     * @param ReinitableConfigInterface $reinitableConfig
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        ListInterface $themeList,
        Config $config,
        ReinitableConfigInterface $reinitableConfig,
        IndexerRegistry $indexerRegistry
    ) {
        $this->themeList = $themeList;
        $this->config = $config;
        $this->reinitableConfig = $reinitableConfig;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->assignTheme();

        $setup->endSetup();
    }

    /**
     * Assign Theme
     *
     * @return void
     * @throws \Exception
     */
    protected function assignTheme()
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->themeList->getThemeByFullPath(self::THEME_FULL_PATH);

        if ($theme->getId()) {
            $this->config->assignToStore($theme, [Store::DEFAULT_STORE_ID], ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
            $this->reinitableConfig->reinit();
            $this->indexerRegistry->get(DesignConfig::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
        }
    }
}
