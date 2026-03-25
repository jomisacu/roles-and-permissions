create table {{permissions_table}}
(
    id          char(36)     not null
        primary key,
    context_id  char(36)     not null,
    name        varchar(191) not null,
    description text         null
);

create table {{actor_permission_relations_table}}
(
    context_id         varchar(36)          not null,
    actor_id           char(36)             not null,
    permission_id      char(36)             not null,
    resource           text                 not null,
    negated            tinyint(1) default 0 not null,
    created_by_user_id char(36)             null,
    created_at         datetime             not null,
    updated_by_user_id char(36)             null,
    updated_at         datetime             null,
    constraint {{actor_permission_relations_permission_fk}}
        foreign key (permission_id) references {{permissions_table}} (id)
            on delete cascade
);

create index {{actor_permission_relations_context_actor_index}}
    on {{actor_permission_relations_table}} (context_id, actor_id);

create index {{permissions_context_index}}
    on {{permissions_table}} (context_id);

create table {{roles_table}}
(
    id          char(36)     not null
        primary key,
    context_id  char(36)     not null,
    name        varchar(191) not null,
    description text         null
);

create table {{actor_role_relations_table}}
(
    context_id         char(36) not null,
    role_id            char(36) not null,
    actor_id           char(36) not null,
    created_by_user_id char(36) null,
    created_at         datetime not null,
    updated_by_user_id char(36) null,
    constraint {{actor_role_relations_role_fk}}
        foreign key (role_id) references {{roles_table}} (id)
            on delete cascade
);

create index {{actor_role_relations_context_role_actor_index}}
    on {{actor_role_relations_table}} (context_id, role_id, actor_id);

create table {{role_permission_relations_table}}
(
    context_id         char(36)             not null,
    role_id            char(36)             not null,
    permission_id      char(36)             not null,
    resource           text                 not null,
    negated            tinyint(1) default 0 not null,
    created_by_user_id char(36)             null,
    created_at         datetime             not null,
    updated_by_user_id char(36)             null,
    updated_at         datetime             null,
    constraint {{role_permission_relations_permission_fk}}
        foreign key (permission_id) references {{permissions_table}} (id)
            on delete cascade,
    constraint {{role_permission_relations_role_fk}}
        foreign key (role_id) references {{roles_table}} (id)
            on delete cascade
);

create index {{role_permission_relations_context_role_index}}
    on {{role_permission_relations_table}} (context_id, role_id);

create index {{roles_context_index}}
    on {{roles_table}} (context_id);
