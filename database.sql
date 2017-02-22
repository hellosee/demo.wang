-- database db_t

-- 用户表
create table `db_t`.`tb_member`(
	`id` int(8) unsigned not null auto_increment comment '自增id',
	`username` varchar(60) not null default '' comment '用户名',
	`password` char(32) not null default '' comment '密码',
	`salt` varchar(8) not null default '' comment '混合码',
	`createtime` int(10) not null default 0 comment '创建时间',
	primary key(`id`)
)engine=myisam default charset=utf8 comment='用户表';

-- 栏目表
create table `db_t`.`tb_category`(
	`id` int(8) unsigned not null auto_increment comment '自增ID',
	`parentid` int(8) unsigned not null auto_increment comment '父类ID',
	`cname` varchar(255) not null default '' comment '栏目名称',
	`createtime` int(10) not null default 0 comment '创建时间',
	primary key(`id`)
)engine=myisam default charset=utf8 comment='产品表';

-- 产品表
create table `db_t`.`tb_product`(
	`id` int(8) unsigned not null auto_increment comment '自增ID',
	`cid` int(8) not null default 0 comment '栏目',
	`name` varchar(255) not null default '' comment '产品名称',
	`createtime` int(10) not null default 0 comment '创建时间',
	primary key(`id`)
)engine=myisam default charset=utf8 comment='产品表';