<?php
namespace app\common\logic\article;
use app\common\logic\Base;
use app\common\model\article\Article as Articlemodel;
use app\common\model\article\ArticleFabulous;
use think\Exception;
use think\facade\Db;

/**
 * Class Article
 * @package app\common\logic\article
 * author:lzcong
 * time:11:58
 * comment:说明
 * package:包命，命名空间
 * todo todolis:类中还需要完善的功能列表
 */
class Article extends Base
{
    public function __construct(Articlemodel $model)
    {
        $this->model = $model;
    }

    public function create(array $data) :void
    {
        if(!(new ArticleCategory())->getCategoryId($data['cid'])) E('分类不存在');
        try {
            Db::transaction(function()use($data){
                $content = $data['content'];
                unset($data['content']);
                $article = $this->model->create($data);
                $article->content()->save(['content' => $content]);
            });
        }catch (Exception $e){
            E($e->getMessage());
        }

    }

    public function update(int $id, array $data)
    {
        if (!$this->model->find($id))E('数据不存在');
        if (!(new ArticleCategory())->getCategoryId($data['cid']))E('分类不存在');
        Db::transaction(function()use($id,$data){
            $content = $data['content'];
            unset($data['content']);
            $this->model->update($data,['id'=>$id]);
            $article = $this->model->find($id);
            $article->content->content = $content;
            $article->together(['content'])->save();
        });
    }

    public function getData($where ,$uid = 0, $field = '*')
    {
        $data = $this->model->search($where)->field($field)->find();
        if(empty($data)){
            return [];
        }
        $data['content'] = $data->content;
        $data['FabulousNum'] = $data->Fabulous->where('status',1)->count();
//        $data['isFabulous'] = (new ArticleFabulous())->isFabulous($uid,$where['id']); //是否被点赞
//        $data['comment'] = $data->comment; //评论
        return $data->toArray();
    }

    public function List($where,$limit)
    {
        $query = $this->model->search($where);
        $list = $query->paginate($limit, false);
//        foreach ($list as $k=> $v)
//        {
//            $list[$k]['Fabulous'] = $v->Fabulous;
//            $list[$k]['FabulousNum'] = $v->Fabulous->where('status',1)->count();
//        }
        return formatPaginate($list);
    }

    public function delete($id){
        if (!$this->model->find($id))E('数据不存在');
        $this->model->destroy($id);
        return $this->model->content()->delete($id);
    }
}