<?php declare(strict_types=1);
namespace openvk\Web\Models\Repositories;
use openvk\Web\Models\RowModel;
use openvk\Web\Models\Entities\User;
use openvk\Web\Models\Entities\Message;
use openvk\Web\Models\Entities\Correspondence;
use Chandler\Database\DatabaseConnection;

class Messages
{
    private $context;
    private $messages;
    
    function __construct()
    {
        $this->context  = DatabaseConnection::i()->getContext();
        $this->messages = $this->context->table("messages");
    }
    
    function getCorrespondencies(RowModel $correspondent, int $page = 1, ?int $perPage = NULL): \Traversable
    {
        $id      = $correspondent->getId();
        $class   = get_class($correspondent);
        $limit   = $perPage ?? OPENVK_DEFAULT_PER_PAGE;
        $offset  = ($page - 1) * $limit;
        $query   = file_get_contents(__DIR__ . "/../sql/get-correspondencies.tsql");
        DatabaseConnection::i()->getConnection()->query(file_get_contents(__DIR__ . "/../sql/mysql-msg-fix.tsql"));
        $coresps = DatabaseConnection::i()->getConnection()->query($query, $id, $class, $id, $class, $limit, $offset);
        foreach($coresps as $c) {
            if($c->class === 'openvk\Web\Models\Entities\User')
                $anotherCorrespondent = (new Users)->get($c->id);
            else if($c->class === 'openvk\Web\Models\Entities\Club')
                $anotherCorrespondent = (new Clubs)->get($c->id);
            
            yield new Correspondence($correspondent, $anotherCorrespondent);
        }
    }
}