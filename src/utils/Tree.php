<?php

namespace ys\utils;

class Tree
{
    protected static $config = [
        'primary_key' => 'id',//主键
        'parent_key' => 'parent_id',    //父键
        'expanded' => false,//是否展开子节点
        'root_visible' => true  //是否显示根节点
    ];

    /**
     * 生成树形结构
     *
     * @param array $data 元数据
     * @param array $options 自定义config
     * @return array
     */
    public static function makeTree(array $data, array $options = [])
    {
        $config = array_merge(self::$config, $options);
        self::$config = $config;
        extract($config);
        //数据归类
        $dataset = [];
        foreach ($data as $item) {
            $id = $item[$primary_key];
            $parent_id = $item[$parent_key];
            $dataset[$parent_id][$id] = $item;
        }
        $r['children'] = self::makeTreeCore(0, $dataset);
        $r = $root_visible ? $r : $r['children'];
        return $r;
    }

    private static function makeTreeCore($index, $data)
    {
        foreach ($data[$index] as $id => $item) {
            if (isset($data[$id])) {
                $item['expanded'] = self::$config['expanded'];
                $item['children'] = self::makeTreeCore($id, $data);
            } else {//叶子节点
                $item['leaf'] = true;
            }
            $arr[] = $item;
        }
        return $arr;
    }
}
// $test = function () {
//     $data = [
//         [
//             'id' => 1,
//             'name' => 'book',
//             'parent_id' => 0
//         ],
//         [
//             'id' => 2,
//             'name' => 'music',
//             'parent_id' => 0
//         ],
//         [
//             'id' => 3,
//             'name' => 'book1',

//             'parent_id' => 1
//         ],
//         [
//             'id' => 4,
//             'name' => 'book2',
//             'parent_id' => 3
//         ]
//     ];
//     $r = Tree::makeTree($data);
//     echo json_encode($r);
// };
