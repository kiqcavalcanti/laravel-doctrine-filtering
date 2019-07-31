# laravel-doctrine-filtering
Apply dynamic query filters using laravel-doctrine

# How to use:

- In your repository class extends DoctrineFilterRepository
- In your Entity class extends FilterEntity
- In your validator you can use the rule isValidFilter to validate the queryBuilder

# Configuration Filter with Joins

In your entity add the joins with this structure:

  protected static $joins = [
        'customer_product' => [
            'entity' => CustomerProduct::class,
            'condition' => 'advertisement.customerProductId = customer_product.id'
        ]
  ];


# Examples

example_1 = [
 url = http://localhost/advertisements?filter[created_at]=2018-07-22T18:48:16-03:00&filter[customer_product][customer][name|like]=cor
 query = 'SELECT advertisement
          FROM entities\Advertisement advertisement INNER JOIN Entities\CustomerProduct customer_product
          WITH advertisement.customerProductId = customer_product.id INNER JOIN Entities\Customer customer
          WITH customer_product.customerId = customer.id
          WHERE advertisement.createdAt = '2018-07-22 18:48:16' AND LOWER(customer.name) LIKE '%cor%' 
];

example_2 = [
 url = http://localhost/advertisements?filter[created_at|between]=2018-07-22T18:48:16-03:00,2018-07-22T18:48:16-03:00
 query = 'SELECT advertisement
          FROM Entities\Advertisement advertisement
          WHERE advertisement.createdAt BETWEEN '2018-07-22 18:48:16' AND '2018-07-22 18:48:16'
];


# OperatorEnum (available Operators)

      case 'eq':
      case '=':
      return OperatorEnum::EQ;

      case 'neq':
      case '!=':
      case '<>':
        return OperatorEnum::NEQ;

      case 'gt':
      case '>':
        return OperatorEnum::GT;

      case 'gte':
      case '>=':
        return OperatorEnum::GTE;

      case 'lt':
      case '<':
        return OperatorEnum::LT;

      case 'lte':
      case '<=':
        return OperatorEnum::LTE;

      case 'between':
        return OperatorEnum::BETWEEN;

      case 'like':
        return OperatorEnum::LIKE;

      case 'in':
        return OperatorEnum::IN;

      case 'notin':
        return OperatorEnum::NOTIN;

      case 'isnull':
        return OperatorEnum::ISNULL;

      case 'isnotnull':
        return OperatorEnum::ISNOTNULL;
        
        
# Usage

In you Repository Class

 $qb = $this->_em->createQueryBuilder()
            ->select($this->getEntityName()::getAlias())
            ->from($this->_entityName, $alias, $indexBy);
            
 $this->applyFilters($qb, $filters);
           
            
            
