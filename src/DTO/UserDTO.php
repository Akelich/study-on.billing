<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    #[Assert\NotBlank(message: 'Email не может быть пустым!')]
    #[Assert\Email(message: 'Email заполнен не верно!')]
    public ?string $username;

    #[Assert\NotBlank(message: 'Пароль не может быть пустым!')]
    #[Assert\Length(min: 6, minMessage: 'Пароль должен быть из не менее {{ limit }} символов..')]
    public ?string $password;
}

// class UserDTO
// {
//     /**
//      * @Assert\NotBlank( message="Email пуст!" )
//      * @Assert\Email( message="Email заполнен не верно." )
//      */
//     public string $username;


//     /**
//      * @Assert\NotBlank(message="Пароль не может быть пустым!")
//      * @Assert\Lenth(min=6, minMessage="Пароль должен быть из не менее {{ limit }} символов.")
//      */
//     public string $password;
// }