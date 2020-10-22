<?php


namespace CriteriaSearch\Loop;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\FeatureAvailability;
use Thelia\Model\Map\FeatureAvTableMap;
use Thelia\Model\Map\FeatureProductTableMap;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\Map\ProductTableMap;

class CriteriaSearchFeatureAvLoop extends FeatureAvailability implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return parent::getArgDefinitions()->addArguments(array(
            Argument::createIntListTypeArgument("category"),
            Argument::createBooleanTypeArgument("return_empty", true),
        ));
    }

        
    public function buildModelCriteria()
    {
        $query = parent::buildModelCriteria();

        if (false === $this->getReturnEmpty()) {
            $featureAvProductJoin = new Join();
            $featureAvProductJoin->addExplicitCondition(
                FeatureAvTableMap::TABLE_NAME,
                'ID',
                null,
                FeatureProductTableMap::TABLE_NAME,
                'FEATURE_AV_ID',
                null
            );

            $featureAvProductJoin->setJoinType(Criteria::JOIN);
            $query->addJoinObject($featureAvProductJoin, 'feature_av_product_join');

            if (null !== $categories = $this->getCategory()) {

                $featureAvProductCategoryJoin = new Join();
                $featureAvProductCategoryJoin->addExplicitCondition(
                    FeatureProductTableMap::TABLE_NAME,
                    'PRODUCT_ID',
                    null,
                    ProductCategoryTableMap::TABLE_NAME,
                    'PRODUCT_ID',
                    null
                );

                $productVisible = new Join();
                $productVisible->addExplicitCondition(
                  FeatureProductTableMap::TABLE_NAME,
                  'PRODUCT_ID',
                  null,
                  ProductTableMap::TABLE_NAME,
                  'ID',
                  null
                );

                $productVisible->setJoinType(Criteria::JOIN);

                $query->addJoinObject($productVisible, 'product_visible')
                  ->addJoinCondition(
                    'product_visible',
                    ProductTableMap::VISIBLE . ' = 1'
                  );

                $query->addJoinObject($featureAvProductCategoryJoin, 'feature_av_product_category_join')
                    ->addJoinCondition(
                        'feature_av_product_category_join',
                        ProductCategoryTableMap::CATEGORY_ID . ' IN (' . implode(',', $categories) . ')'
                    );
            }
        }

        $query->groupById();

        return $query;
    }
    
    
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        return parent::parseResults($loopResult);
    }
}
