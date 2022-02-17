<?php
namespace app\common\logic\article;
use app\common\logic\Base;
use app\common\model\article\Article as Articlemodel;
use app\common\model\article\ArticleFabulous;
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
        Db::transaction(function()use($data){
            $content = $data['content'];
            unset($data['content']);
            $article = $this->model->create($data);
            $article->content()->save(['content' => $content]);
        });
    }

    public function update(int $id, array $data)
    {
        if (!$this->model->find($id))E('数据不存在');
        if (!(new ArticleCategory())->getCategoryId($data['cid']))E('分类不存在');
        Db::transaction(function()use($id,$data){
            $content = $data['content'];
            unset($data['content']);
            $this->model->save($data,['id'=>$id]);
            $article = $this->model->find($id);
            $article->content->content = $content;
            $article->together(['content'])->save();
        });
    }

    public function getData($where ,$uid = 0, $field = '*')
    {
        $data = $this->model->search($where)->field($field)->find();
        $data['FabulousNum'] = $data->Fabulous->where('status',1)->count();
        $data['isFabulous'] = (new ArticleFabulous())->isFabulous($uid,$where['id']);
        return $data;
    }

    public function List($where,$limit,$page)
    {
        $query = $this->model->search($where);
        $list = $query->paginate($limit, false, ['page' => $page]);
        foreach ($list as $k=> $v)
        {
            $list[$k]['Fabulous'] = $v->Fabulous;
            $list[$k]['FabulousNum'] = $v->Fabulous->where('status',1)->count();
        }
        return formatPaginate($list);
    }

    public function delete($id){
        if (!$this->model->find($id))E('数据不存在');
        return $this->model->destroy($id);
    }
}