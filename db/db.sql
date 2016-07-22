PGDMP                         t            new.zip    9.4.8    9.5.1                 0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                       false                       0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                       false                       1262    36215    new.zip    DATABASE     {   CREATE DATABASE "new.zip" WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';
    DROP DATABASE "new.zip";
          	   zip.admin    false                        2615    2200    public    SCHEMA        CREATE SCHEMA public;
    DROP SCHEMA public;
          	   zip.admin    false                        0    0    SCHEMA public    COMMENT     6   COMMENT ON SCHEMA public IS 'standard public schema';
               	   zip.admin    false    7            !           0    0    public    ACL     �   REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM "zip.admin";
GRANT ALL ON SCHEMA public TO "zip.admin";
GRANT ALL ON SCHEMA public TO PUBLIC;
               	   zip.admin    false    7                        3079    11893    plpgsql 	   EXTENSION     ?   CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
    DROP EXTENSION plpgsql;
                  false            "           0    0    EXTENSION plpgsql    COMMENT     @   COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';
                       false    1            �            1259    104540    comments    TABLE     �   CREATE TABLE comments (
    comments_id integer NOT NULL,
    comments_author integer NOT NULL,
    comments_location text NOT NULL,
    comments_text text NOT NULL,
    comments_date timestamp with time zone,
    comments_parent_id integer
);
    DROP TABLE public.comments;
       public      	   zip.admin    false    7            �            1259    104545    comments_comments_id_seq    SEQUENCE     z   CREATE SEQUENCE comments_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 /   DROP SEQUENCE public.comments_comments_id_seq;
       public    	   zip.admin    false    7    179            #           0    0    comments_comments_id_seq    SEQUENCE OWNED BY     G   ALTER SEQUENCE comments_comments_id_seq OWNED BY comments.comments_id;
            public    	   zip.admin    false    180            �            1259    44084    news    TABLE     �   CREATE TABLE news (
    news_id integer NOT NULL,
    news_date date NOT NULL,
    news_header character varying(200) NOT NULL,
    news_text text,
    news_author character varying(100) DEFAULT 'default'::character varying
);
    DROP TABLE public.news;
       public      	   zip.admin    false    7            �            1259    44082    news_id_seq    SEQUENCE     m   CREATE SEQUENCE news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 "   DROP SEQUENCE public.news_id_seq;
       public    	   zip.admin    false    7    174            $           0    0    news_id_seq    SEQUENCE OWNED BY     2   ALTER SEQUENCE news_id_seq OWNED BY news.news_id;
            public    	   zip.admin    false    173            �            1259    52066    publs    TABLE     }   CREATE TABLE publs (
    publs_id integer NOT NULL,
    publs_header character varying(200) NOT NULL,
    publs_text text
);
    DROP TABLE public.publs;
       public      	   zip.admin    false    7            �            1259    52064    publs_publs_id_seq    SEQUENCE     t   CREATE SEQUENCE publs_publs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 )   DROP SEQUENCE public.publs_publs_id_seq;
       public    	   zip.admin    false    7    176            %           0    0    publs_publs_id_seq    SEQUENCE OWNED BY     ;   ALTER SEQUENCE publs_publs_id_seq OWNED BY publs.publs_id;
            public    	   zip.admin    false    175            �            1259    60776    users    TABLE     �   CREATE TABLE users (
    user_login character varying(100) NOT NULL,
    user_password character varying NOT NULL,
    user_group character varying(100),
    user_id integer NOT NULL,
    user_email character varying NOT NULL
);
    DROP TABLE public.users;
       public      	   zip.admin    false    7            �            1259    60784    users_users_id_seq    SEQUENCE     t   CREATE SEQUENCE users_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 )   DROP SEQUENCE public.users_users_id_seq;
       public    	   zip.admin    false    177    7            &           0    0    users_users_id_seq    SEQUENCE OWNED BY     :   ALTER SEQUENCE users_users_id_seq OWNED BY users.user_id;
            public    	   zip.admin    false    178            �           2604    104547    comments_id    DEFAULT     n   ALTER TABLE ONLY comments ALTER COLUMN comments_id SET DEFAULT nextval('comments_comments_id_seq'::regclass);
 C   ALTER TABLE public.comments ALTER COLUMN comments_id DROP DEFAULT;
       public    	   zip.admin    false    180    179            �           2604    44087    news_id    DEFAULT     Y   ALTER TABLE ONLY news ALTER COLUMN news_id SET DEFAULT nextval('news_id_seq'::regclass);
 ;   ALTER TABLE public.news ALTER COLUMN news_id DROP DEFAULT;
       public    	   zip.admin    false    174    173    174            �           2604    52069    publs_id    DEFAULT     b   ALTER TABLE ONLY publs ALTER COLUMN publs_id SET DEFAULT nextval('publs_publs_id_seq'::regclass);
 =   ALTER TABLE public.publs ALTER COLUMN publs_id DROP DEFAULT;
       public    	   zip.admin    false    176    175    176            �           2604    60786    user_id    DEFAULT     a   ALTER TABLE ONLY users ALTER COLUMN user_id SET DEFAULT nextval('users_users_id_seq'::regclass);
 <   ALTER TABLE public.users ALTER COLUMN user_id DROP DEFAULT;
       public    	   zip.admin    false    178    177            �           2606    104552    comments_id 
   CONSTRAINT     T   ALTER TABLE ONLY comments
    ADD CONSTRAINT comments_id PRIMARY KEY (comments_id);
 >   ALTER TABLE ONLY public.comments DROP CONSTRAINT comments_id;
       public      	   zip.admin    false    179    179            �           2606    44092    news_id 
   CONSTRAINT     H   ALTER TABLE ONLY news
    ADD CONSTRAINT news_id PRIMARY KEY (news_id);
 6   ALTER TABLE ONLY public.news DROP CONSTRAINT news_id;
       public      	   zip.admin    false    174    174            �           2606    52074    publs_id 
   CONSTRAINT     K   ALTER TABLE ONLY publs
    ADD CONSTRAINT publs_id PRIMARY KEY (publs_id);
 8   ALTER TABLE ONLY public.publs DROP CONSTRAINT publs_id;
       public      	   zip.admin    false    176    176            �           2606    104335    unique_email 
   CONSTRAINT     L   ALTER TABLE ONLY users
    ADD CONSTRAINT unique_email UNIQUE (user_email);
 <   ALTER TABLE ONLY public.users DROP CONSTRAINT unique_email;
       public      	   zip.admin    false    177    177            �           2606    104324    unique_login 
   CONSTRAINT     L   ALTER TABLE ONLY users
    ADD CONSTRAINT unique_login UNIQUE (user_login);
 <   ALTER TABLE ONLY public.users DROP CONSTRAINT unique_login;
       public      	   zip.admin    false    177    177            �           2606    60794    users_id 
   CONSTRAINT     J   ALTER TABLE ONLY users
    ADD CONSTRAINT users_id PRIMARY KEY (user_id);
 8   ALTER TABLE ONLY public.users DROP CONSTRAINT users_id;
       public      	   zip.admin    false    177    177            �           2606    104553    comments_author    FK CONSTRAINT     v   ALTER TABLE ONLY comments
    ADD CONSTRAINT comments_author FOREIGN KEY (comments_author) REFERENCES users(user_id);
 B   ALTER TABLE ONLY public.comments DROP CONSTRAINT comments_author;
       public    	   zip.admin    false    179    177    1953            �           2606    104815 	   parent_id    FK CONSTRAINT     z   ALTER TABLE ONLY comments
    ADD CONSTRAINT parent_id FOREIGN KEY (comments_parent_id) REFERENCES comments(comments_id);
 <   ALTER TABLE ONLY public.comments DROP CONSTRAINT parent_id;
       public    	   zip.admin    false    179    1955    179           